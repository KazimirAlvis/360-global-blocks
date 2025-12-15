import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import iconCatalog from './health-icons.json';
import './editor.scss';

export default function Edit(props) {
	const { attributes, setAttributes } = props;
	const {
		mainTitle = 'Why Choose Us',
		cards = [
			{ icon: 'body/heart_organ', title: 'Expert Care', text: 'Professional healthcare services.' },
			{ icon: 'people/doctor', title: 'Compassionate', text: 'We care about your wellbeing.' },
			{ icon: 'devices/stethoscope', title: 'Professional', text: 'Experienced medical team.' },
		],
	} = attributes;

	const blockProps = useBlockProps({ className: 'info-cards-block' });

	const updateCard = (cardIndex, field, value) => {
		const newCards = [...cards];
		newCards[cardIndex] = { ...newCards[cardIndex], [field]: value };
		setAttributes({ cards: newCards });
	};

	const availableIcons = iconCatalog || {};

	const iconOptions = [
		{ label: __('None', 'global-360-theme'), value: '' },
		...Object.entries(availableIcons).map(([key, icon]) => ({
		label: `${icon.name} (${icon.category})`,
		value: key,
		})),
	];

	const renderIconPreview = (iconKey) => {
		if (!iconKey) {
			return 'None';
		}
		// Simple icon preview for admin - just show the name
		const iconData = availableIcons[iconKey];
		if (iconData) {
			return iconData.name;
		}
		return 'Icon';
	};

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody
					title="Settings"
					initialOpen={true}
				>
					<TextControl
						label="Main Title"
						value={mainTitle}
						onChange={(value) => setAttributes({ mainTitle: value })}
						__next40pxDefaultSize={true}
						__nextHasNoMarginBottom={true}
					/>
					{cards.map((card, index) => (
						<div
							key={index}
							style={{ marginBottom: '16px', padding: '8px', border: '1px solid #ddd' }}
						>
							<h4>Card {index + 1}</h4>
							<SelectControl
								label="Icon"
								value={card.icon}
								options={iconOptions}
								onChange={(value) => updateCard(index, 'icon', value)}
							/>
							<TextControl
								label="Title"
								value={card.title}
								onChange={(value) => updateCard(index, 'title', value)}
								__next40pxDefaultSize={true}
								__nextHasNoMarginBottom={true}
							/>
							<TextControl
								label="Text"
								value={card.text}
								onChange={(value) => updateCard(index, 'text', value)}
								__next40pxDefaultSize={true}
								__nextHasNoMarginBottom={true}
							/>
						</div>
					))}
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div className="info-cards-container">
					<RichText
						tagName="h2"
						className="info-cards-main-title"
						value={mainTitle}
						onChange={(value) => setAttributes({ mainTitle: value })}
						placeholder="Enter main title..."
					/>
					<div className="info-cards-grid">
						{cards.map((card, index) => (
							<div
								key={index}
								className="info-card"
							>
								<div className="info-card-icon-wrapper">
									<div className="info-card-icon-preview">{renderIconPreview(card.icon)}</div>
								</div>
								<RichText
									tagName="h3"
									className="info-card-title"
									value={card.title}
									onChange={(value) => updateCard(index, 'title', value)}
									placeholder="Enter title..."
								/>
								<RichText
									tagName="p"
									className="info-card-text"
									value={card.text}
									onChange={(value) => updateCard(index, 'text', value)}
									placeholder="Enter text..."
								/>
							</div>
						))}
					</div>
				</div>
			</div>
		</Fragment>
	);
}
