import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import {
	InnerBlocks,
	InspectorControls,
	PanelColorSettings,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import metadata from '../block.json';
import columnMetadata from '../column/block.json';
import './style.scss';
import './editor.scss';

const ALLOWED_COLUMN_BLOCKS = ['core/heading', 'core/paragraph', 'core/list', 'core/quote', 'core/buttons'];

const normalizeColorValue = (value) => {
	const raw = typeof value === 'string' ? value.trim() : '';
	if (!raw) {
		return '';
	}

	if (/^var\(--[a-zA-Z0-9_-]+\)$/.test(raw)) {
		return raw;
	}

	if (typeof window !== 'undefined' && window.CSS && typeof window.CSS.supports === 'function') {
		if (window.CSS.supports('color', raw)) {
			return raw;
		}
	}

	return '';
};

const COLUMN_TEMPLATE = [
	['core/heading', { placeholder: __('Add heading…', 'global360blocks') }],
	['core/paragraph', { placeholder: __('Add supporting copy…', 'global360blocks') }],
];

const COLUMNS_TEMPLATE = [
	[
		'global360blocks/two-column-text-column',
		{
			columnKey: 'left',
			backgroundColor: '#f6f7fb',
			lock: {
				move: false,
				remove: true,
			},
		},
		COLUMN_TEMPLATE,
	],
	[
		'global360blocks/two-column-text-column',
		{
			columnKey: 'right',
			backgroundColor: '#ffffff',
			lock: {
				move: false,
				remove: true,
			},
		},
		COLUMN_TEMPLATE,
	],
];

registerBlockType(columnMetadata, {
	edit({ attributes, setAttributes }) {
		const { columnKey = '', backgroundColor = '' } = attributes;
		const safeBackgroundColor = normalizeColorValue(backgroundColor);
		const resolvedBackground = safeBackgroundColor || (columnKey === 'right' ? '#ffffff' : '#f6f7fb');
		const label =
			columnKey === 'right'
				? __('Right Column', 'global360blocks')
				: columnKey === 'left'
				? __('Left Column', 'global360blocks')
				: __('Column', 'global360blocks');

		const columnClasses = ['two-column-text__column'];
		if (columnKey) {
			columnClasses.push(`two-column-text__column--${columnKey}`);
		}

		const blockProps = useBlockProps({
			className: columnClasses.join(' '),
			style: {
				'--two-column-text-column-bg': resolvedBackground,
				backgroundColor: resolvedBackground,
			},
		});

		const innerBlocksProps = useInnerBlocksProps(
			{
				className: 'two-column-text__column-inner',
			},
			{
				allowedBlocks: ALLOWED_COLUMN_BLOCKS,
				template: COLUMN_TEMPLATE,
				templateInsertUpdatesSelection: true,
				orientation: 'vertical',
			}
		);

		return (
			<>
				<InspectorControls>
					<PanelColorSettings
						title={__('Background color', 'global360blocks')}
						initialOpen
						colorSettings={[
							{
								label: __('Background color', 'global360blocks'),
								value: safeBackgroundColor,
								onChange: (value) => setAttributes({ backgroundColor: normalizeColorValue(value) }),
								fallbackValue: resolvedBackground,
							},
						]}
					/>
					<PanelBody
						title={__('Custom color code', 'global360blocks')}
						initialOpen={false}
					>
						<TextControl
							label={__('Hex or CSS color', 'global360blocks')}
							help={__('Enter a color code like #0ea5e9 or an rgba()/var() value.', 'global360blocks')}
							placeholder={resolvedBackground}
							value={backgroundColor}
							onChange={(value) => setAttributes({ backgroundColor: value })}
							onBlur={(event) => setAttributes({ backgroundColor: normalizeColorValue(event?.target?.value) })}
						/>
					</PanelBody>
				</InspectorControls>
				<div {...blockProps}>
					<div className="two-column-text__column-label">{label}</div>
					<div {...innerBlocksProps} />
				</div>
			</>
		);
	},

	save({ attributes }) {
		const { columnKey = '', backgroundColor = '' } = attributes;
		const columnClasses = ['two-column-text__column'];
		if (columnKey) {
			columnClasses.push(`two-column-text__column--${columnKey}`);
		}

		const blockProps = useBlockProps.save({
			className: columnClasses.join(' '),
			style: backgroundColor ? { '--two-column-text-column-bg': backgroundColor, backgroundColor } : undefined,
		});

		return (
			<div {...blockProps}>
				<div className="two-column-text__column-inner">
					<InnerBlocks.Content />
				</div>
			</div>
		);
	},
});

registerBlockType(metadata, {
	edit() {
		const blockProps = useBlockProps({ className: 'two-column-text' });

		return (
			<div {...blockProps}>
				<InnerBlocks
					allowedBlocks={['global360blocks/two-column-text-column']}
					template={COLUMNS_TEMPLATE}
					templateLock="all"
					renderAppender={false}
				/>
			</div>
		);
	},

	save() {
		return (
			<div {...useBlockProps.save({ className: 'two-column-text' })}>
				<InnerBlocks.Content />
			</div>
		);
	},
});
