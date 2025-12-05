import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { InspectorControls, PanelColorSettings, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

const DEFAULT_ITEM = {
	question: __('What question should we answer?', 'global360blocks'),
	answer: __('Provide a concise answer that helps patients quickly understand expectations.', 'global360blocks'),
};

const Edit = ({ attributes, setAttributes }) => {
	const { heading, items = [], backgroundColor, activeItemColor } = attributes;
	const availableColors = useSelect((select) => select('core/block-editor')?.getSettings()?.colors || [], []);

	useEffect(() => {
		if (!items.length) {
			setAttributes({ items: [DEFAULT_ITEM] });
		}
	}, [items.length, setAttributes]);

	const blockProps = useBlockProps({
		className: 'faq-accordion-block',
		style: {
			'--faq-background': backgroundColor || undefined,
			'--faq-active-background': activeItemColor || undefined,
		},
	});

	const updateItem = (index, field, value) => {
		const nextItems = [...items];
		nextItems[index] = { ...nextItems[index], [field]: value };
		setAttributes({ items: nextItems });
	};

	const addItem = () => {
		setAttributes({
			items: [
				...items,
				{
					question: __('New question', 'global360blocks'),
					answer: __('Share the supporting information here.', 'global360blocks'),
				},
			],
		});
	};

	const removeItem = (index) => {
		const nextItems = items.filter((_, idx) => idx !== index);
		setAttributes({ items: nextItems.length ? nextItems : [DEFAULT_ITEM] });
	};

	const moveItem = (index, direction) => {
		const newIndex = index + direction;
		if (newIndex < 0 || newIndex >= items.length) {
			return;
		}

		const nextItems = [...items];
		const [movedItem] = nextItems.splice(index, 1);
		nextItems.splice(newIndex, 0, movedItem);
		setAttributes({ items: nextItems });
	};

	return (
		<>
			<InspectorControls>
				<PanelColorSettings
					title={__('Colors', 'global360blocks')}
					colors={availableColors}
					colorSettings={[
						{
							label: __('Background', 'global360blocks'),
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value || '' }),
						},
						{
							label: __('Open dropdown', 'global360blocks'),
							value: activeItemColor,
							onChange: (value) => setAttributes({ activeItemColor: value || '' }),
						},
					]}
				/>
				<PanelBody
					title={__('Content', 'global360blocks')}
					initialOpen={false}
				>
					<p>{__('Use the controls inside each row to reorder or remove entries.', 'global360blocks')}</p>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<RichText
					tagName="h2"
					className="faq-accordion-heading"
					value={heading}
					onChange={(value) => setAttributes({ heading: value })}
					placeholder={__('Add a section heading…', 'global360blocks')}
					allowedFormats={['core/bold', 'core/italic', 'core/link', 'core/underline', 'core/text-color']}
				/>
				<div
					className="faq-accordion-list"
					role="tablist"
				>
					{items.map((item, index) => (
						<div
							className="faq-accordion-item"
							key={`faq-item-${index}`}
						>
							<div className="faq-item-actions">
								<Button
									onClick={() => moveItem(index, -1)}
									disabled={index === 0}
									isSmall
									variant="secondary"
								>
									{__('Move up', 'global360blocks')}
								</Button>
								<Button
									onClick={() => moveItem(index, 1)}
									disabled={index === items.length - 1}
									isSmall
									variant="secondary"
								>
									{__('Move down', 'global360blocks')}
								</Button>
								<Button
									onClick={() => removeItem(index)}
									isSmall
									variant="link"
								>
									{__('Remove', 'global360blocks')}
								</Button>
							</div>
							<div className="faq-question">
								<RichText
									tagName="span"
									className="faq-question-text"
									value={item.question}
									onChange={(value) => updateItem(index, 'question', value)}
									placeholder={__('Question', 'global360blocks')}
									allowedFormats={[
										'core/bold',
										'core/italic',
										'core/link',
										'core/strikethrough',
										'core/text-color',
									]}
								/>
								<span
									className="faq-chevron"
									aria-hidden="true"
								/>
							</div>
							<div
								className="faq-answer"
								role="region"
							>
								<span className="faq-answer-label">{__('Answer', 'global360blocks')}</span>
								<RichText
									tagName="div"
									className="faq-answer-inner"
									value={item.answer}
									onChange={(value) => updateItem(index, 'answer', value)}
									placeholder={__('Answer content…', 'global360blocks')}
									allowedFormats={[
										'core/bold',
										'core/italic',
										'core/link',
										'core/underline',
										'core/text-color',
									]}
								/>
							</div>
						</div>
					))}
				</div>
				<Button
					onClick={addItem}
					variant="primary"
					className="faq-add-item"
				>
					{__('Add question', 'global360blocks')}
				</Button>
			</div>
		</>
	);
};

const Save = () => null;

export default Edit;
export { Save };
