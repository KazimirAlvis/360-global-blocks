import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from '@wordpress/element';
import {
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	InnerBlocks,
	InspectorControls,
	PanelColorSettings,
} from '@wordpress/block-editor';
import '@wordpress/format-library';
import { Button, PanelBody, RadioControl, ToggleControl } from '@wordpress/components';
import { registerBlockType, rawHandler, createBlock } from '@wordpress/blocks';
import { useSelect, useDispatch } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import './style.css';
import './editor.css';

const BODY_TEMPLATE = [['core/paragraph', { placeholder: __('Add body content…', 'global360blocks') }]];

const BODY_ALLOWED_BLOCKS = ['core/paragraph', 'core/list', 'core/heading', 'core/quote', 'core/html'];

const stripTags = (value = '') => (typeof value === 'string' ? value.replace(/<[^>]*>?/gm, '') : '');

const Edit = ({ attributes, setAttributes, clientId }) => {
	const {
		imageUrl,
		imageId,
		heading,
		bodyText,
		layout = 'image-left',
		backgroundColor,
		headingColor,
		highPriorityImage = false,
	} = attributes;

	const innerBlocks = useSelect((select) => select('core/block-editor').getBlocks(clientId), [clientId]);
	const availableColors = useSelect((select) => {
		const settings = select('core/block-editor')?.getSettings?.();
		return settings?.colors || [];
	}, []);
	const hasInnerBlocks = innerBlocks.length > 0;

	const { replaceInnerBlocks } = useDispatch('core/block-editor');

	useEffect(() => {
		if (!hasInnerBlocks && bodyText) {
			const parsedBlocks = rawHandler({ HTML: bodyText });
			let nextBlocks = parsedBlocks.length
				? parsedBlocks
				: [createBlock('core/html', { content: bodyText })];

			if (
				parsedBlocks.length > 1 &&
				parsedBlocks.every(
					(block) => block?.name === 'core/paragraph' && typeof block?.attributes?.content === 'string'
				)
			) {
				const combinedContent = parsedBlocks
					.map((block) => block.attributes.content.trim())
					.filter(Boolean)
					.join('<br />');

				if (combinedContent) {
					nextBlocks = [createBlock('core/paragraph', { content: combinedContent })];
				}
			}

			replaceInnerBlocks(clientId, nextBlocks, false);
			setAttributes({ bodyText: '' });
		}
	}, [hasInnerBlocks, bodyText, replaceInnerBlocks, clientId, setAttributes]);

	const onSelectImage = (media) => {
		setAttributes({
			imageUrl: media.url,
			imageId: media.id,
		});
	};

	const onRemoveImage = () => {
		setAttributes({
			imageUrl: '',
			imageId: 0,
		});
	};

	const mediaDetails = useSelect((select) => (imageId ? select('core').getMedia(imageId) : null), [imageId]);

	const previewUrl = useMemo(() => {
		// Prefer the attachment record so the editor mirrors responsive server output.
		if (imageUrl) {
			return imageUrl;
		}
		if (!mediaDetails) {
			return '';
		}
		const mediaInfo = mediaDetails?.media_details;
		return (
			mediaDetails?.source_url ||
			mediaInfo?.sizes?.full?.source_url ||
			mediaInfo?.sizes?.large?.source_url ||
			mediaInfo?.file ||
			''
		);
	}, [imageUrl, mediaDetails]);

	const derivedImageAlt = useMemo(() => {
		const candidates = [];

		if (mediaDetails) {
			const maybeAlt = typeof mediaDetails.alt === 'string' ? mediaDetails.alt : '';
			const maybeAltText = typeof mediaDetails.alt_text === 'string' ? mediaDetails.alt_text : '';
			const maybeRenderedTitle =
				typeof mediaDetails?.title?.rendered === 'string' ? mediaDetails.title.rendered : '';
			const maybeTitle = typeof mediaDetails.title === 'string' ? mediaDetails.title : '';
			candidates.push(maybeAlt, maybeAltText, maybeRenderedTitle, maybeTitle);
		}

		const headingFallback = decodeEntities(stripTags(heading || '').trim());
		candidates.push(headingFallback);

		const match = candidates.find((candidate) => candidate && candidate.trim());
		return match ? match.trim() : '';
	}, [mediaDetails, heading]);

	const blockProps = useBlockProps({
		className: 'two-column-block',
		style: {
			backgroundColor: backgroundColor || undefined,
		},
	});

	const renderImageColumn = () => (
		<div className="two-column-image">
			{previewUrl ? (
				<img
					src={previewUrl}
					alt={derivedImageAlt}
					className="column-image"
					loading={highPriorityImage ? 'eager' : 'lazy'}
					fetchpriority={highPriorityImage ? 'high' : 'auto'}
				/>
			) : (
				<div className="image-placeholder">
					<span>{__('Image will appear here', 'global360blocks')}</span>
				</div>
			)}
			<div className="image-controls">
				<MediaUploadCheck>
					<MediaUpload
						onSelect={onSelectImage}
						allowedTypes={['image']}
						value={imageId}
						render={({ open }) => (
							<div className="upload-controls">
								{!previewUrl && (
									<Button
										className="button button-large"
										onClick={open}
									>
										{__('Upload Image', 'global360blocks')}
									</Button>
								)}
								{previewUrl && (
									<>
										<Button
											className="button"
											onClick={open}
										>
											{__('Replace Image', 'global360blocks')}
										</Button>
										<Button
											className="button"
											onClick={onRemoveImage}
										>
											{__('Remove Image', 'global360blocks')}
										</Button>
									</>
								)}
							</div>
						)}
					/>
				</MediaUploadCheck>
			</div>
		</div>
	);

	const renderContentColumn = () => (
		<div
			className="two-column-content"
			style={backgroundColor ? { backgroundColor } : undefined}
		>
			<div className="two-column-content-inner">
				<RichText
					identifier="heading"
					tagName="h2"
					className="two-column-heading"
					style={headingColor ? { color: headingColor } : undefined}
					value={heading}
					onChange={(value) => setAttributes({ heading: value })}
					placeholder={__('Enter heading...', 'global360blocks')}
					allowedFormats={['core/bold', 'core/italic', 'core/text-color', 'core/link']}
				/>

				<div className="two-column-body-field">
					<span className="two-column-field-label">{__('Body content', 'global360blocks')}</span>
					<div className="two-column-body">
						<InnerBlocks
							allowedBlocks={BODY_ALLOWED_BLOCKS}
							template={BODY_TEMPLATE}
							templateLock={false}
						/>
					</div>
				</div>

				<div className="two-column-button-preview">
					<span className="btn btn_global">{__('Take Risk Assessment Now', 'global360blocks')}</span>
				</div>
			</div>
		</div>
	);

	return (
		<>
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
							{ label: __('Image on left', 'global360blocks'), value: 'image-left' },
							{ label: __('Image on right', 'global360blocks'), value: 'image-right' },
						]}
					/>
				</PanelBody>
				<PanelBody title={__('Performance', 'global360blocks')}>
					<ToggleControl
						label={__('High priority image (hero / above the fold)', 'global360blocks')}
						checked={!!highPriorityImage}
						onChange={(value) => setAttributes({ highPriorityImage: !!value })}
					/>
				</PanelBody>
				<PanelColorSettings
					title={__('Colors', 'global360blocks')}
					colors={availableColors}
					colorSettings={[
						{
							label: __('Heading color', 'global360blocks'),
							value: headingColor,
							onChange: (value) => setAttributes({ headingColor: value || '' }),
						},
						{
							label: __('Content background', 'global360blocks'),
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value || '' }),
						},
					]}
				/>
			</InspectorControls>
			<div {...blockProps}>
				<div className={`two-column-container layout-${layout}`}>
					{renderImageColumn()}
					{renderContentColumn()}
				</div>
			</div>
		</>
	);
};

// Dynamic block – frontend rendering happens in PHP for responsive images.
const Save = () => null;

registerBlockType('global360blocks/two-column', {
	edit: Edit,
	save: Save,
});
