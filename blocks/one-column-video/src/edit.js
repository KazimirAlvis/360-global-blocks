import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { useBlockProps, InspectorControls, PanelColorSettings, RichText } from '@wordpress/block-editor';
import { TextControl, PanelBody } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

function getYouTubeEmbedUrl(url) {
	if (!url) return '';

	let videoId = '';

	if (url.includes('youtube.com/watch?v=')) {
		videoId = url.split('v=')[1]?.split('&')[0] || '';
	} else if (url.includes('youtu.be/')) {
		videoId = url.split('youtu.be/')[1]?.split('?')[0] || '';
	} else if (url.includes('youtube.com/embed/')) {
		return url;
	}

	if (!videoId) {
		return '';
	}

	const params = new URLSearchParams({
		rel: '0',
		modestbranding: '1',
		playsinline: '1',
	});

	return `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
}

export default function Edit({ attributes, setAttributes }) {
	const { heading, videoUrl, backgroundColor } = attributes;

	const availableColors = useSelect((select) => {
		const settings = select('core/block-editor')?.getSettings?.();
		return settings?.colors || [];
	}, []);

	const embedUrl = useMemo(() => getYouTubeEmbedUrl(videoUrl), [videoUrl]);

	const blockProps = useBlockProps({
		className: 'one-column-video',
		style: backgroundColor ? { '--one-column-video-background': backgroundColor } : undefined,
	});

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody
					title={__('Video', 'global360blocks')}
					initialOpen={true}
				>
					<TextControl
						label={__('YouTube URL', 'global360blocks')}
						value={videoUrl}
						onChange={(value) => setAttributes({ videoUrl: value || '' })}
						placeholder={__('Paste a YouTube link…', 'global360blocks')}
						help={__('Supports youtube.com and youtu.be links.', 'global360blocks')}
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

			<div className="one-column-video-inner">
				<RichText
					tagName="h2"
					className="one-column-video-heading"
					value={heading}
					onChange={(value) => setAttributes({ heading: value })}
					placeholder={__('Enter heading…', 'global360blocks')}
					allowedFormats={['core/bold', 'core/italic']}
				/>

				<div className="one-column-video-media">
					{embedUrl ? (
						<div className="one-column-video-embed">
							<iframe
								src={embedUrl}
								className="one-column-video-iframe"
								frameBorder="0"
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
								allowFullScreen
								title={__('YouTube video', 'global360blocks')}
							/>
						</div>
					) : (
						<div className="one-column-video-placeholder">
							{__('Video preview will appear here', 'global360blocks')}
						</div>
					)}
				</div>
			</div>
		</div>
	);
}
