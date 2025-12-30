<?php
/**
 * Reusable image collector for Global 360 blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Global360Blocks_Image_Collector' ) ) {

    class Global360Blocks_Image_Collector {

        const DEFAULT_LIMIT = 20;

        /**
         * Singleton instance.
         *
         * @var Global360Blocks_Image_Collector|null
         */
        private static $instance = null;

        /**
         * Simple in-request cache keyed by "post_id:limit".
         *
         * @var array<string, array>
         */
        private $post_cache = array();

        /**
         * Retrieve the singleton instance.
         *
         * @return Global360Blocks_Image_Collector
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Collect images for a given post.
         *
         * @param mixed $post  WP_Post, ID, array or null.
         * @param int   $limit Maximum images to return.
         *
         * @return array[]
         */
        public function get_images_for_post( $post = null, $limit = self::DEFAULT_LIMIT ) {
            $post_obj = global360blocks_resolve_post_object( $post );
            if ( ! $post_obj instanceof WP_Post ) {
                return array();
            }

            $limit    = $this->sanitize_limit( $limit );
            $cache_id = $post_obj->ID . ':' . $limit;

            if ( isset( $this->post_cache[ $cache_id ] ) ) {
                return $this->post_cache[ $cache_id ];
            }

            $images = $this->collect_from_content( $post_obj->post_content, $limit );
            $images = apply_filters( 'global360blocks_block_images_for_post', $images, $post_obj, $limit );

            $this->post_cache[ $cache_id ] = $images;

            return $images;
        }

        /**
         * Collect images from serialized block content.
         *
         * @param string $content Serialized post content.
         * @param int    $limit   Limit for collected images.
         *
         * @return array[]
         */
        public function collect_from_content( $content, $limit = self::DEFAULT_LIMIT ) {
            $limit = $this->sanitize_limit( $limit );

            if ( ! is_string( $content ) || '' === $content ) {
                return array();
            }

            if ( ! function_exists( 'has_blocks' ) || ! function_exists( 'parse_blocks' ) ) {
                return array();
            }

            if ( ! has_blocks( $content ) ) {
                return array();
            }

            $blocks = parse_blocks( $content );
            if ( empty( $blocks ) || ! is_array( $blocks ) ) {
                return array();
            }

            return $this->collect_from_blocks( $blocks, $limit );
        }

        /**
         * Internal collector that walks parsed blocks.
         *
         * @param array $blocks Parsed blocks array.
         * @param int   $limit  Limit for collected images.
         *
         * @return array[]
         */
        private function collect_from_blocks( array $blocks, $limit ) {
            $collection = array();
            $seen       = array();

            foreach ( $blocks as $block ) {
                if ( count( $collection ) >= $limit ) {
                    break;
                }

                if ( ! is_array( $block ) ) {
                    continue;
                }

                $block_name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';

                if ( 0 === strpos( $block_name, 'global360blocks/' ) ) {
                    $attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
                    $this->collect_from_attrs( $attrs, $collection, $seen, $limit );
                }

                if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
                    $nested = $this->collect_from_blocks( $block['innerBlocks'], $limit );
                    foreach ( $nested as $candidate ) {
                        $this->register_candidate( $candidate, $collection, $seen, $limit );
                        if ( count( $collection ) >= $limit ) {
                            break;
                        }
                    }
                }
            }

            return array_slice( $collection, 0, $limit );
        }

        /**
         * Inspect a flat attribute array.
         *
         * @param array $attrs Attribute array.
         * @param array $collection Reference collection.
         * @param array $seen       Dedupe registry.
         * @param int   $limit      Limit for collected images.
         *
         * @return void
         */
        private function collect_from_attrs( $attrs, array &$collection, array &$seen, $limit ) {
            if ( empty( $attrs ) || ! is_array( $attrs ) || count( $collection ) >= $limit ) {
                return;
            }

            foreach ( $attrs as $key => $value ) {
                $this->scan_attribute_value( $value, is_string( $key ) ? $key : '', $collection, $seen, $limit );
                if ( count( $collection ) >= $limit ) {
                    return;
                }
            }
        }

        /**
         * Recursively scan an attribute value.
         *
         * @param mixed  $value      Attribute value.
         * @param string $key        Key describing the value.
         * @param array  $collection Reference collection.
         * @param array  $seen       Dedupe registry.
         * @param int    $limit      Limit for collected images.
         *
         * @return void
         */
        private function scan_attribute_value( $value, $key, array &$collection, array &$seen, $limit ) {
            if ( count( $collection ) >= $limit ) {
                return;
            }

            if ( is_array( $value ) ) {
                $candidate = $this->build_candidate_from_array( $value );
                if ( $candidate ) {
                    $this->register_candidate( $candidate, $collection, $seen, $limit );
                }

                foreach ( $value as $child_key => $child_value ) {
                    $this->scan_attribute_value( $child_value, is_string( $child_key ) ? $child_key : $key, $collection, $seen, $limit );
                    if ( count( $collection ) >= $limit ) {
                        return;
                    }
                }

                return;
            }

            if ( is_numeric( $value ) && $this->attr_key_is_image_id( $key ) ) {
                $this->register_candidate(
                    array(
                        'attachment_id' => (int) $value,
                        'url'           => '',
                        'alt'           => '',
                    ),
                    $collection,
                    $seen,
                    $limit
                );
                return;
            }

            if ( ! is_string( $value ) ) {
                return;
            }

            $trimmed = trim( $value );
            if ( '' === $trimmed ) {
                return;
            }

            if ( $this->attr_key_is_image_alt( $key ) && ! empty( $collection ) ) {
                $last_index = count( $collection ) - 1;
                if ( '' === $collection[ $last_index ]['alt'] ) {
                    $collection[ $last_index ]['alt'] = $trimmed;
                }
                return;
            }

            if ( $this->is_probable_image_url( $trimmed ) && ( '' === $key || $this->attr_key_is_image_url( $key ) ) ) {
                $this->register_candidate(
                    array(
                        'attachment_id' => 0,
                        'url'           => $trimmed,
                        'alt'           => '',
                    ),
                    $collection,
                    $seen,
                    $limit
                );
            }
        }

        /**
         * Register a candidate image while enforcing dedupe + limit.
         *
         * @param array $candidate Candidate description.
         * @param array $collection Collection reference.
         * @param array $seen       Dedupe registry.
         * @param int   $limit      Limit for collected images.
         *
         * @return void
         */
        private function register_candidate( array $candidate, array &$collection, array &$seen, $limit ) {
            if ( count( $collection ) >= $limit ) {
                return;
            }

            $prepared = $this->prepare_candidate( $candidate );
            if ( ! $prepared ) {
                return;
            }

            $dedupe_key = $prepared['attachment_id'] ? 'id:' . $prepared['attachment_id'] : 'url:' . strtolower( $prepared['url'] );
            if ( isset( $seen[ $dedupe_key ] ) ) {
                return;
            }

            $collection[]        = $prepared;
            $seen[ $dedupe_key ] = true;
        }

        /**
         * Normalize a candidate by fetching URLs + metadata.
         *
         * @param array $candidate Candidate data.
         *
         * @return array|null
         */
        private function prepare_candidate( array $candidate ) {
            $attachment_id = isset( $candidate['attachment_id'] ) ? (int) $candidate['attachment_id'] : 0;
            $url           = isset( $candidate['url'] ) ? trim( (string) $candidate['url'] ) : '';
            $alt           = isset( $candidate['alt'] ) ? trim( wp_strip_all_tags( (string) $candidate['alt'] ) ) : '';
            $width         = isset( $candidate['width'] ) ? (int) $candidate['width'] : 0;
            $height        = isset( $candidate['height'] ) ? (int) $candidate['height'] : 0;

            if ( '' !== $url && ! $this->is_probable_image_url( $url ) ) {
                $url = '';
            }

            if ( $attachment_id > 0 ) {
                $image_src = wp_get_attachment_image_src( $attachment_id, 'full' );
                if ( is_array( $image_src ) && ! empty( $image_src[0] ) ) {
                    if ( '' === $url ) {
                        $url = $image_src[0];
                    }
                    if ( $width <= 0 && isset( $image_src[1] ) ) {
                        $width = (int) $image_src[1];
                    }
                    if ( $height <= 0 && isset( $image_src[2] ) ) {
                        $height = (int) $image_src[2];
                    }
                }

                if ( '' === $alt ) {
                    $maybe_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
                    if ( is_string( $maybe_alt ) ) {
                        $alt = $maybe_alt;
                    }
                }
            }

            if ( '' === $url ) {
                return null;
            }

            return array(
                'attachment_id' => $attachment_id,
                'url'           => $url,
                'alt'           => $alt,
                'width'         => $width,
                'height'        => $height,
                'source'        => isset( $candidate['source'] ) ? $candidate['source'] : 'global360blocks',
            );
        }

        /**
         * Build a candidate from a nested attribute array.
         *
         * @param array $value Nested attribute array.
         *
         * @return array|null
         */
        private function build_candidate_from_array( array $value ) {
            $candidate = array(
                'attachment_id' => 0,
                'url'           => '',
                'alt'           => '',
            );

            $id_keys = array( 'id', 'imageId', 'imageID', 'imgId', 'attachmentId', 'attachmentID', 'mediaId', 'mediaID', 'desktopImageId', 'mobileImageId', 'heroImageId', 'logoId' );
            foreach ( $id_keys as $id_key ) {
                if ( isset( $value[ $id_key ] ) && is_numeric( $value[ $id_key ] ) ) {
                    $candidate['attachment_id'] = (int) $value[ $id_key ];
                    break;
                }
            }

            $url_keys = array( 'url', 'src', 'source', 'imageUrl', 'imageURL', 'imageSrc', 'imageSRC', 'desktopImage', 'mobileImage', 'desktopImageUrl', 'mobileImageUrl', 'backgroundImage', 'backgroundUrl', 'heroImage', 'heroImageUrl', 'mediaUrl', 'logoUrl' );
            foreach ( $url_keys as $url_key ) {
                if ( isset( $value[ $url_key ] ) && $this->is_probable_image_url( $value[ $url_key ] ) ) {
                    $candidate['url'] = $value[ $url_key ];
                    break;
                }
            }

            $alt_keys = array( 'alt', 'imageAlt', 'imageALT', 'imageAltText', 'altText', 'heroImageAlt', 'mediaAlt', 'logoAlt' );
            foreach ( $alt_keys as $alt_key ) {
                if ( isset( $value[ $alt_key ] ) && is_string( $value[ $alt_key ] ) ) {
                    $candidate['alt'] = $value[ $alt_key ];
                    break;
                }
            }

            if ( ! $candidate['attachment_id'] && '' === $candidate['url'] ) {
                return null;
            }

            return $candidate;
        }

        /**
         * Rough heuristic for attachment-id style keys.
         *
         * @param string $key Attribute key.
         *
         * @return bool
         */
        private function attr_key_is_image_id( $key ) {
            if ( '' === $key ) {
                return false;
            }

            return (bool) preg_match( '/(image|photo|hero|banner|media|background|thumbnail|logo)[a-z0-9_]*id$/i', $key );
        }

        /**
         * Rough heuristic for URL/src keys.
         *
         * @param string $key Attribute key.
         *
         * @return bool
         */
        private function attr_key_is_image_url( $key ) {
            if ( '' === $key ) {
                return false;
            }

            if ( in_array( strtolower( $key ), array( 'url', 'src', 'image', 'imageurl', 'imagesrc' ), true ) ) {
                return true;
            }

            return (bool) preg_match( '/(image|photo|hero|banner|media|background|thumbnail|logo)[a-z0-9_]*(url|src|source)$/i', $key );
        }

        /**
         * Rough heuristic for alt-text keys.
         *
         * @param string $key Attribute key.
         *
         * @return bool
         */
        private function attr_key_is_image_alt( $key ) {
            if ( '' === $key ) {
                return false;
            }

            return (bool) preg_match( '/(image|photo|hero|banner|media|background|thumbnail|logo)[a-z0-9_]*alt$/i', $key );
        }

        /**
         * Determine if a value is likely an image URL/data URI.
         *
         * @param string $value Potential URL.
         *
         * @return bool
         */
        private function is_probable_image_url( $value ) {
            if ( ! is_string( $value ) ) {
                return false;
            }

            $value = trim( $value );
            if ( '' === $value ) {
                return false;
            }

            if ( 0 === strpos( $value, 'data:image/' ) ) {
                return true;
            }

            if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
                return false;
            }

            $path = wp_parse_url( $value, PHP_URL_PATH );
            $path = is_string( $path ) ? $path : '';
            $path = $path ? preg_split( '/[?#]/', $path, 2 )[0] : '';
            $extension = strtolower( pathinfo( (string) $path, PATHINFO_EXTENSION ) );

            if ( '' === $extension ) {
                return false;
            }

            $allowed = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'heic', 'heif' );

            return in_array( $extension, $allowed, true );
        }

        /**
         * Sanitize the requested limit.
         *
         * @param int $limit Requested limit.
         *
         * @return int
         */
        private function sanitize_limit( $limit ) {
            $limit = (int) $limit;
            if ( $limit <= 0 ) {
                $limit = self::DEFAULT_LIMIT;
            }

            return min( $limit, 50 );
        }
    }
}
