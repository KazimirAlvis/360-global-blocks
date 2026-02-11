import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, Button, ToggleControl } from '@wordpress/components';
import './editor.css';

export default function Edit({ attributes, setAttributes }) {
	const {
		bgImageUrl,
		bgImageId,
		heading,
		subheading,
		highPriorityImage = true,
	} = attributes;
	const blockProps = useBlockProps({ className: 'full-hero-block' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Hero Image', 'global360blocks')}>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) => setAttributes({ bgImageUrl: media.url, bgImageId: media.id })}
							allowedTypes={['image']}
							value={bgImageId}
							render={({ open }) => (
								<Button
									onClick={open}
									isSecondary
								>
									{bgImageUrl
										? __('Change Image', 'global360blocks')
										: __('Select Image', 'global360blocks')}
								</Button>
							)}
						/>
					</MediaUploadCheck>
					<ToggleControl
						label={__('High priority image (hero / above the fold)', 'global360blocks')}
						checked={!!highPriorityImage}
						onChange={(value) => setAttributes({ highPriorityImage: !!value })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				{bgImageUrl && (
					<div className="full-hero-media">
						<img
							src={bgImageUrl}
							alt=""
							loading={highPriorityImage ? 'eager' : 'lazy'}
							fetchpriority={highPriorityImage ? 'high' : 'auto'}
							decoding="async"
						/>
					</div>
				)}
				{!bgImageUrl && (
					<div className="full-hero-placeholder">
						<MediaUploadCheck>
							<MediaUpload
								onSelect={(media) => setAttributes({ bgImageUrl: media.url, bgImageId: media.id })}
								allowedTypes={['image']}
								value={bgImageId}
								render={({ open }) => (
									<Button
										onClick={open}
										isPrimary
										className="full-hero-upload-btn"
									>
										{__('Upload Hero Image', 'global360blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
					</div>
				)}
				<div className="full-hero-content">
					<RichText
						tagName="h1"
						className="full-hero-heading"
						value={heading}
						onChange={(value) => setAttributes({ heading: value })}
						placeholder={__('Add heading...', 'global360blocks')}
					/>
					<RichText
						tagName="p"
						className="full-hero-subheading"
						value={subheading}
						onChange={(value) => setAttributes({ subheading: value })}
						placeholder={__('Add sub-heading...', 'global360blocks')}
					/>
					<Button
						className="full-hero-assess-btn btn btn_global"
						disabled
					>
						{__('Take Risk Assessment Now', 'global360blocks')}
					</Button>
				</div>
			</div>
		</>
	);
}
