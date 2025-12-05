import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, RichText, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ColorPicker } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './editor.scss';
import './style.scss';

registerBlockType('global360blocks/two-column-cta', {
	edit: ({ attributes, setAttributes }) => {
		const { heading, buttonText, buttonUrl, backgroundColor } = attributes;

		const blockProps = useBlockProps({
			className: 'two-column-cta',
			style: {
				backgroundColor: backgroundColor || undefined,
			},
		});

		const ALLOWED_BLOCKS = ['core/paragraph', 'core/heading', 'core/list', 'core/quote'];

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Button Settings', '360-global-blocks')}>
						<TextControl
							label={__('Button Text', '360-global-blocks')}
							value={buttonText}
							onChange={(value) => setAttributes({ buttonText: value })}
						/>
						<TextControl
							label={__('Button URL', '360-global-blocks')}
							value={buttonUrl}
							onChange={(value) => setAttributes({ buttonUrl: value })}
							type="url"
						/>
					</PanelBody>
					<PanelBody title={__('Background Color', '360-global-blocks')}>
						<ColorPicker
							color={backgroundColor}
							onChangeComplete={(value) => setAttributes({ backgroundColor: value.hex })}
							disableAlpha
						/>
						{backgroundColor && (
							<button
								className="components-button is-secondary"
								onClick={() => setAttributes({ backgroundColor: '' })}
								style={{ marginTop: '10px' }}
							>
								{__('Clear Color', '360-global-blocks')}
							</button>
						)}
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<div className="two-column-cta__inner">
						<div className="two-column-cta__content">
							<RichText
								tagName="h2"
								className="two-column-cta__heading"
								placeholder={__('Add heading...', '360-global-blocks')}
								value={heading}
								onChange={(value) => setAttributes({ heading: value })}
								allowedFormats={[]}
							/>
							<div className="two-column-cta__body">
								<InnerBlocks
									allowedBlocks={ALLOWED_BLOCKS}
									template={[['core/paragraph', { placeholder: 'Add content...' }]]}
								/>
							</div>
						</div>
						<div className="two-column-cta__button-wrapper">
							<div className="two-column-cta__button-preview">
								{buttonText || __('Take Risk Assessment Now', '360-global-blocks')}
							</div>
						</div>
					</div>
				</div>
			</>
		);
	},

	save: () => {
		return <InnerBlocks.Content />;
	},
});
