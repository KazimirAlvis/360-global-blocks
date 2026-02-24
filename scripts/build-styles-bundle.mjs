import fs from 'node:fs';
import path from 'node:path';

const rootDir = path.resolve(process.cwd());
const blocksDir = path.join(rootDir, 'blocks');
const outputFile = path.join(rootDir, 'assets', 'css', '360-blocks-bundle.css');

function fileExists(filePath) {
	try {
		fs.accessSync(filePath, fs.constants.R_OK);
		return true;
	} catch {
		return false;
	}
}

function listBlockStyleFiles() {
	if (!fileExists(blocksDir)) {
		return [];
	}

	const blockFolders = fs
		.readdirSync(blocksDir, { withFileTypes: true })
		.filter((entry) => entry.isDirectory())
		.map((entry) => entry.name)
		.sort((a, b) => a.localeCompare(b));

	const styleFiles = [];
	for (const folder of blockFolders) {
		const styleFile = path.join(blocksDir, folder, 'build', 'style-index.css');
		if (fileExists(styleFile)) {
			styleFiles.push(styleFile);
		}
	}

	return styleFiles;
}

function normalizeCss(css) {
	// Keep it simple: preserve CSS as-built, just ensure it ends with a newline.
	if (!css.endsWith('\n')) {
		return css + '\n';
	}
	return css;
}

function buildBundle() {
	const header = [
		'/*',
		' * 360 Global Blocks — Frontend Styles Bundle',
		' * This file is auto-generated. Do not edit directly.',
		` * Generated: ${new Date().toISOString()}`,
		' */',
		'',
	].join('\n');

	const inputs = [];

	// Include shared styles first so block-specific CSS can override as needed.
	const sharedCss = path.join(rootDir, 'assets', 'css', 'global-shared.min.css');
	if (fileExists(sharedCss)) {
		inputs.push(sharedCss);
	}

	inputs.push(...listBlockStyleFiles());

	let output = header;
	for (const input of inputs) {
		const rel = path.relative(rootDir, input);
		const css = fs.readFileSync(input, 'utf8');
		output += `\n/* === ${rel.replaceAll('*/', '* /')} === */\n`;
		output += normalizeCss(css);
	}

	fs.mkdirSync(path.dirname(outputFile), { recursive: true });
	fs.writeFileSync(outputFile, output, 'utf8');

	console.log(`Built CSS bundle: ${path.relative(rootDir, outputFile)}`);
	console.log(`Inputs: ${inputs.length}`);
}

buildBundle();
