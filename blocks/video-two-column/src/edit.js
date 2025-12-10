import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	PanelColorSettings,
	InnerBlocks,
} from '@wordpress/block-editor';
import { TextControl, Button, PanelBody, RadioControl } from '@wordpress/components';
import { registerBlockType, rawHandler, createBlock } from '@wordpress/blocks';
import { useSelect, useDispatch } from '@wordpress/data';
import './style.css';
import './editor.css';

const BODY_TEMPLATE = [['core/paragraph', { placeholder: __('Add body content…', 'global360blocks') }]];
const BODY_ALLOWED_BLOCKS = ['core/paragraph', 'core/list', 'core/heading', 'core/quote'];

const Edit = ({ attributes, setAttributes, clientId }) => {
	const { videoUrl, heading, bodyText, videoTitle, layout = 'media-left', backgroundColor } = attributes;

	const availableColors = useSelect((select) => {
		const settings = select('core/block-editor')?.getSettings?.();
		return settings?.colors || [];
	}, []);

	const innerBlocks = useSelect((select) => select('core/block-editor').getBlocks(clientId), [clientId]);
	const hasInnerBlocks = innerBlocks.length > 0;
	const { replaceInnerBlocks } = useDispatch('core/block-editor');

	useEffect(() => {
		if (hasInnerBlocks || !bodyText) {
			return;
		}

		const parsedBlocks = rawHandler({ HTML: bodyText }) || [];
		const nextBlocks = parsedBlocks.length
			? parsedBlocks
			: [
				createBlock('core/paragraph', {
					content: bodyText,
				}),
			];

		replaceInnerBlocks(clientId, nextBlocks, false);
		setAttributes({ bodyText: '' });
	}, [hasInnerBlocks, bodyText, replaceInnerBlocks, clientId, setAttributes]);

	const onChangeVideoUrl = (url) => {
		setAttributes({
			videoUrl: url,
		});
	};

	const onRemoveVideo = () => {
		setAttributes({
			videoUrl: '',
		});
	};

	// Function to convert YouTube URL to embed URL
	const getYouTubeEmbedUrl = (url) => {
		if (!url) return '';

		// Handle different YouTube URL formats
		let videoId = '';

		if (url.includes('youtube.com/watch?v=')) {
			videoId = url.split('v=')[1]?.split('&')[0];
		} else if (url.includes('youtu.be/')) {
			videoId = url.split('youtu.be/')[1]?.split('?')[0];
		} else if (url.includes('youtube.com/embed/')) {
			return url; // Already an embed URL
		}

		if (!videoId) {
			return url;
		}

		const params = new URLSearchParams({
			rel: '0',
			modestbranding: '1',
			playsinline: '1',
		});

		return `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
	};

	// Check if URL is a YouTube URL
	const isYouTubeUrl = (url) => {
		return url.includes('youtube.com') || url.includes('youtu.be');
	};

	const renderVideo = () => {
		if (!videoUrl) {
			return (
				<div className="video-placeholder">
					<span>Video will appear here</span>
				</div>
			);
		}

		if (isYouTubeUrl(videoUrl)) {
			const embedUrl = getYouTubeEmbedUrl(videoUrl);
			return (
				<div className="video-wrapper">
					<iframe
						src={embedUrl}
						frameBorder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
						allowFullScreen
						className="youtube-video"
					/>
				</div>
			);
		}

		// For direct video files
		return (
			<div className="video-wrapper">
				<video
					controls
					className="column-video"
					src={videoUrl}
				>
					Your browser does not support the video tag.
				</video>
			</div>
		);
	};

	const blockProps = useBlockProps({
		className: 'video-two-column-block',
		style: backgroundColor ? { backgroundColor } : undefined,
	});

	const videoColumnStyles = {
		display: 'flex',
		flexDirection: 'column',
		alignItems: 'stretch',
		justifyContent: 'flex-start',
		gap: '16px',
	};

	const videoControlsStyles = {
		width: '100%',
		position: 'relative',
	};

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody
					title={__('Layout', 'global360blocks')}
					initialOpen={true}
				>
					<RadioControl
						label={__('Media position', 'global360blocks')}
						selected={layout}
						onChange={(value) => setAttributes({ layout: value })}
						options={[
							{ label: __('Video on left', 'global360blocks'), value: 'media-left' },
							{ label: __('Video on right', 'global360blocks'), value: 'media-right' },
						]}
					/>
				</PanelBody>
				<PanelColorSettings
					title={__('Background', 'global360blocks')}
					colors={availableColors}
					colorSettings={[
						{
							label: __('Background color', 'global360blocks'),
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value || '' }),
						},
					]}
				/>
			</InspectorControls>
			<div className={`video-two-column-container layout-${layout}`}>
				<div
					className="video-two-column-video"
					style={videoColumnStyles}
				>
					<RichText
						tagName="h2"
						className="video-two-column-video-title"
						value={videoTitle}
						onChange={(value) => setAttributes({ videoTitle: value })}
						placeholder={__('Add video title...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic', 'core/link']}
					/>
					{renderVideo()}
					<div
						className="video-controls"
						style={videoControlsStyles}
					>
						<TextControl
							label={__('Video URL', 'global360blocks')}
							value={videoUrl}
							onChange={onChangeVideoUrl}
							placeholder={__('Paste YouTube URL or direct video link...', 'global360blocks')}
							help={__('Supports YouTube links and direct video file URLs', 'global360blocks')}
						/>
						{videoUrl && (
							<Button
								className="button"
								onClick={onRemoveVideo}
							>
								{__('Remove Video', 'global360blocks')}
							</Button>
						)}
					</div>
				</div>

				<div className="video-two-column-content">
					<RichText
						tagName="h2"
						className="video-two-column-heading"
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
						placeholder={__('Enter heading...', 'global360blocks')}
						allowedFormats={['core/bold', 'core/italic']}
					/>

					<div className="video-two-column-body-field">
						<span className="video-two-column-field-label">{__('Body content', 'global360blocks')}</span>
						<div className="video-two-column-body">
							<InnerBlocks
								allowedBlocks={BODY_ALLOWED_BLOCKS}
								template={BODY_TEMPLATE}
								templateLock={false}
							/>
						</div>
					</div>

					<div className="video-two-column-button-preview">
						<span className="btn btn_global">Take Risk Assessment Now</span>
					</div>
				</div>
			</div>
		</div>
	);
};

const Save = () => <InnerBlocks.Content />;

registerBlockType('global360blocks/video-two-column', {
	edit: Edit,
	save: Save,
});
