import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { InspectorControls, PanelColorSettings, RichText, useBlockProps } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

const DEFAULT_REVIEW = {
	name: __('Patient name', 'global360blocks'),
	clinic: __('Clinic name', 'global360blocks'),
	review: __('Write the patient review…', 'global360blocks'),
};

export default function Edit({ attributes, setAttributes }) {
	const { heading, backgroundColor, reviews = [] } = attributes;
	const availableColors = useSelect((select) => select('core/block-editor')?.getSettings()?.colors || [], []);

	useEffect(() => {
		if (!reviews.length) {
			setAttributes({ reviews: [DEFAULT_REVIEW] });
		}
	}, [reviews.length, setAttributes]);

	const blockProps = useBlockProps({
		className: 'patient-reviews-slider',
		style: {
			'--reviews-background': backgroundColor || undefined,
		},
	});

	const updateReview = (index, field, value) => {
		const next = [...reviews];
		next[index] = { ...next[index], [field]: value };
		setAttributes({ reviews: next });
	};

	const addReview = () => {
		setAttributes({ reviews: [...reviews, { ...DEFAULT_REVIEW }] });
	};

	const removeReview = (index) => {
		const next = reviews.filter((_, idx) => idx !== index);
		setAttributes({ reviews: next.length ? next : [DEFAULT_REVIEW] });
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
					]}
				/>
			</InspectorControls>
			<div {...blockProps}>
				<div className="patient-reviews-slider-inner">
					<div className="patient-reviews-slider-heading">
						<RichText
							tagName="h2"
							value={heading}
							onChange={(value) => setAttributes({ heading: value })}
							placeholder={__('Add a header…', 'global360blocks')}
							allowedFormats={[
								'core/bold',
								'core/italic',
								'core/link',
								'core/underline',
								'core/text-color',
							]}
						/>
					</div>

					<div className="patient-reviews-slider-editor-list">
						{reviews.map((item, index) => (
							<div
								className="patient-review-editor-item"
								key={`patient-review-${index}`}
							>
								<div className="patient-review-editor-actions">
									<Button
										onClick={() => removeReview(index)}
										isSmall
										variant="link"
									>
										{__('Remove', 'global360blocks')}
									</Button>
								</div>

								<RichText
									tagName="div"
									className="patient-review-editor-text"
									value={item.review}
									onChange={(value) => updateReview(index, 'review', value)}
									placeholder={__('Review…', 'global360blocks')}
									allowedFormats={[
										'core/bold',
										'core/italic',
										'core/link',
										'core/underline',
										'core/text-color',
									]}
								/>

								<div className="patient-review-editor-meta">
									<RichText
										tagName="p"
										className="patient-review-editor-name"
										value={item.name}
										onChange={(value) => updateReview(index, 'name', value)}
										placeholder={__('Patient name', 'global360blocks')}
										allowedFormats={[]}
									/>
									<RichText
										tagName="p"
										className="patient-review-editor-clinic"
										value={item.clinic}
										onChange={(value) => updateReview(index, 'clinic', value)}
										placeholder={__('Clinic name', 'global360blocks')}
										allowedFormats={[]}
									/>
								</div>
							</div>
						))}
					</div>

					<Button
						onClick={addReview}
						variant="primary"
						className="patient-reviews-add"
					>
						{__('Add review', 'global360blocks')}
					</Button>
				</div>
			</div>
		</>
	);
}
