import { __ } from '@wordpress/i18n';
import { Fragment, useEffect, useMemo } from '@wordpress/element';
import { InspectorControls, PanelColorSettings, RichText, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';

const MIN_COLUMNS = 2;
const MAX_COLUMNS = 6;
const DEFAULT_COLUMNS = 4;
const MIN_ROWS = 2;
const MAX_ROWS = 10;
const DEFAULT_ROWS = 4;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const resizeArray = (source = [], size, filler = '') => {
	const next = Array.isArray(source) ? source.slice(0, size) : [];
	while (next.length < size) {
		next.push(typeof filler === 'function' ? filler(next.length) : filler);
	}
	return next;
};

const createRow = (columnCount) => ({
	cells: Array.from({ length: columnCount }, () => ''),
});

const normalizeRows = (rows, columnCount, targetLength) => {
	const safeRows = Array.isArray(rows)
		? rows.map((row = {}) => ({
			cells: resizeArray(row.cells, columnCount, ''),
		}))
		: [];

	const trimmed = safeRows.slice(0, targetLength);
	while (trimmed.length < targetLength) {
		trimmed.push(createRow(columnCount));
	}

	return trimmed;
};

const Edit = ({ attributes, setAttributes }) => {
	const {
		heading,
		subheading,
		columns = [],
		rows = [],
		footnote,
		backgroundColor,
		headerBackgroundColor,
		headerFontSize,
	} = attributes;
	const columnCount = columns.length ? columns.length : DEFAULT_COLUMNS;
	const rowCount = rows.length ? rows.length : DEFAULT_ROWS;

	useEffect(() => {
		if (!Array.isArray(columns) || columns.length < MIN_COLUMNS) {
			const currentLength = Array.isArray(columns) ? columns.length : 0;
			const targetLength = currentLength > 0 ? currentLength : DEFAULT_COLUMNS;
			setAttributes({ columns: resizeArray(columns, targetLength, '') });
		}
	}, [columns, setAttributes]);

	useEffect(() => {
		const needsMinimumRows = !Array.isArray(rows) || rows.length < MIN_ROWS;
		const needsCellSync = Array.isArray(rows)
			? rows.some((row) => !Array.isArray(row?.cells) || row.cells.length !== columnCount)
			: true;

		if (!needsMinimumRows && !needsCellSync) {
			return;
		}

		const desiredLength = needsMinimumRows ? Math.max(rows?.length || 0, DEFAULT_ROWS) : rows.length;
		const nextRows = normalizeRows(rows, columnCount, Math.max(desiredLength, MIN_ROWS));
		setAttributes({ rows: nextRows });
	}, [rows, columnCount, setAttributes]);

	const blockStyle = {
		...(backgroundColor ? { '--comparison-background': backgroundColor } : null),
		...(headerBackgroundColor ? { '--comparison-header-background': headerBackgroundColor } : null),
		...(headerFontSize ? { '--comparison-header-font-size': `${headerFontSize}px` } : null),
	};

	const blockProps = useBlockProps({
		className: 'comparison-table-block',
		style: Object.keys(blockStyle).length ? blockStyle : undefined,
	});

	const handleColumnCountChange = (nextCount) => {
		const size = clamp(nextCount, MIN_COLUMNS, MAX_COLUMNS);
		const nextColumns = resizeArray(columns, size, '');
		const nextRows = normalizeRows(rows, size, Math.max(rows.length || 0, MIN_ROWS));
		setAttributes({ columns: nextColumns, rows: nextRows });
	};

	const handleRowCountChange = (nextCount) => {
		const size = clamp(nextCount, MIN_ROWS, MAX_ROWS);
		const nextRows = normalizeRows(rows, columnCount, size);
		setAttributes({ rows: nextRows });
	};

	const updateColumn = (index, value) => {
		const nextColumns = resizeArray(columns, columnCount, '');
		nextColumns[index] = value;
		setAttributes({ columns: nextColumns });
	};

	const updateCell = (rowIndex, columnIndex, value) => {
		const nextRows = rows.map((row, currentIndex) => {
			if (currentIndex !== rowIndex) {
				return row;
			}
			const nextCells = resizeArray(row?.cells, columnCount, '');
			nextCells[columnIndex] = value;
			return { ...row, cells: nextCells };
		});
		setAttributes({ rows: nextRows });
	};

	const columnLabels = useMemo(() => resizeArray(columns, columnCount, ''), [columns, columnCount]);
	const tableRows = useMemo(() => normalizeRows(rows, columnCount, rowCount), [rows, columnCount, rowCount]);

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={__('Table Layout', 'global360blocks')}>
					<RangeControl
						label={__('Number of columns', 'global360blocks')}
						min={MIN_COLUMNS}
						max={MAX_COLUMNS}
						value={columnCount}
						onChange={handleColumnCountChange}
						help={__('Adjust the column labels first, then fill each row below.', 'global360blocks')}
					/>
					<RangeControl
						label={__('Number of rows', 'global360blocks')}
						min={MIN_ROWS}
						max={MAX_ROWS}
						value={rowCount}
						onChange={handleRowCountChange}
						help={__('Add or remove comparison rows as needed.', 'global360blocks')}
					/>
				</PanelBody>
				<PanelBody title={__('Header Row', 'global360blocks')} initialOpen={false}>
					<RangeControl
						label={__('Header font size (px)', 'global360blocks')}
						min={12}
						max={28}
						value={headerFontSize || 0}
						onChange={(value) => setAttributes({ headerFontSize: value || 0 })}
						help={__('Controls the font size of the top header row (column headings).', 'global360blocks')}
					/>
				</PanelBody>
				<PanelColorSettings
					title={__('Background color', 'global360blocks')}
					initialOpen={false}
					colorSettings={[
						{
							label: __('Background color', 'global360blocks'),
							value: backgroundColor,
							onChange: (value) => setAttributes({ backgroundColor: value || '' }),
						},
						{
							label: __('Header row background', 'global360blocks'),
							value: headerBackgroundColor,
							onChange: (value) => setAttributes({ headerBackgroundColor: value || '' }),
						},
					]}
				/>
			</InspectorControls>

			<div {...blockProps}>
				<div className="comparison-table-inner">
					<div className="comparison-table-heading">
						<RichText
							tagName="h2"
							value={heading}
							onChange={(value) => setAttributes({ heading: value })}
							placeholder={__('Comparison title…', 'global360blocks')}
							allowedFormats={["core/bold", "core/italic", "core/link", "core/text-color"]}
						/>
						<RichText
							tagName="p"
							className="comparison-table-subheading"
							value={subheading}
							onChange={(value) => setAttributes({ subheading: value })}
							placeholder={__('Optional supporting copy…', 'global360blocks')}
							allowedFormats={["core/bold", "core/italic", "core/link", "core/text-color"]}
						/>
					</div>

					<div className="comparison-table-wrapper">
						<table className="comparison-table" role="grid">
						<thead>
							<tr>
								{columnLabels.map((label, index) => (
									<th key={`column-${index}`}>
										<RichText
											tagName="span"
											className="comparison-table-cell-input"
											value={label}
											onChange={(value) => updateColumn(index, value)}
											placeholder={__('Column heading', 'global360blocks')}
											allowedFormats={["core/bold", "core/italic", "core/link", "core/text-color"]}
										/>
									</th>
								))}
							</tr>
						</thead>
						<tbody>
							{tableRows.map((row, rowIndex) => (
								<tr key={`row-${rowIndex}`}>
									{columnLabels.map((_, columnIndex) => (
										<td key={`cell-${rowIndex}-${columnIndex}`}>
											<RichText
												tagName="span"
												className="comparison-table-cell-input"
												value={row.cells?.[columnIndex] || ''}
												onChange={(value) => updateCell(rowIndex, columnIndex, value)}
												placeholder={__('Cell detail', 'global360blocks')}
												allowedFormats={["core/bold", "core/italic", "core/link", "core/text-color"]}
											/>
										</td>
									))}
								</tr>
							))}
						</tbody>
					</table>
					</div>

					<RichText
						tagName="p"
						className="comparison-table-footnote"
						value={footnote}
						onChange={(value) => setAttributes({ footnote: value })}
						placeholder={__('Optional footnote or disclaimer…', 'global360blocks')}
						allowedFormats={["core/bold", "core/italic", "core/link", "core/text-color"]}
					/>
				</div>
			</div>
		</Fragment>
	);
};

export default Edit;
