import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useCallback } from '@wordpress/element';

import metadata from '../block.json';

import './editor.scss';
import './style.scss';

const URL_REGEX = /((?:https?:\/\/|www\.)[^\s<]+)$/i;

function normalizeHref(rawUrl) {
	if (rawUrl.startsWith('www.')) {
		return `https://${rawUrl}`;
	}

	return rawUrl;
}

function stripTrailingPunctuation(rawUrl) {
	let url = rawUrl;
	while (url.length) {
		const last = url[url.length - 1];
		if (!['.', ',', ';', ':', '!', '?', ')', ']', '}', '"', "'"].includes(last)) {
			break;
		}
		url = url.slice(0, -1);
	}
	return url;
}

function isProbablyUrl(maybeUrl) {
	if (!URL_REGEX.test(maybeUrl)) {
		return false;
	}

	try {
		// eslint-disable-next-line no-new
		new URL(normalizeHref(maybeUrl));
		return true;
	} catch (error) {
		return false;
	}
}

function linkifyTextNode(textNode, doc) {
	const text = textNode.nodeValue || '';
	if (!text.trim()) {
		return null;
	}

	const matches = [];
	const regex = /(?:https?:\/\/|www\.)[^\s<]+/gi;
	let match;
	while ((match = regex.exec(text))) {
		matches.push({ start: match.index, end: match.index + match[0].length, raw: match[0] });
	}

	if (!matches.length) {
		return null;
	}

	const fragment = doc.createDocumentFragment();
	let cursor = 0;

	for (const found of matches) {
		if (found.start > cursor) {
			fragment.appendChild(doc.createTextNode(text.slice(cursor, found.start)));
		}

		const stripped = stripTrailingPunctuation(found.raw);
		const trailing = found.raw.slice(stripped.length);

		if (stripped && isProbablyUrl(stripped)) {
			const href = normalizeHref(stripped);
			const anchor = doc.createElement('a');
			anchor.setAttribute('href', href);
			anchor.setAttribute('target', '_blank');
			anchor.setAttribute('rel', 'noopener noreferrer');
			anchor.textContent = stripped;
			fragment.appendChild(anchor);
		} else {
			fragment.appendChild(doc.createTextNode(found.raw));
		}

		if (trailing) {
			fragment.appendChild(doc.createTextNode(trailing));
		}

		cursor = found.end;
	}

	if (cursor < text.length) {
		fragment.appendChild(doc.createTextNode(text.slice(cursor)));
	}

	return fragment;
}

function linkifyHtmlPreservingAnchors(html) {
	if (!html) {
		return html;
	}

	// save() runs in the browser; this guard keeps builds/tests safe.
	if (typeof window === 'undefined' || typeof window.DOMParser === 'undefined') {
		return html;
	}

	const parser = new window.DOMParser();
	const doc = parser.parseFromString(`<div>${html}</div>`, 'text/html');
	const container = doc.body.firstElementChild;
	if (!container) {
		return html;
	}

	const walker = doc.createTreeWalker(container, window.NodeFilter.SHOW_TEXT);
	const textNodes = [];
	let current;
	while ((current = walker.nextNode())) {
		textNodes.push(current);
	}

	for (const textNode of textNodes) {
		const parent = textNode.parentElement;
		if (!parent) {
			continue;
		}

		// Preserve existing anchors and avoid messing with code-like content.
		if (parent.closest('a, code, pre, script, style')) {
			continue;
		}

		const fragment = linkifyTextNode(textNode, doc);
		if (!fragment) {
			continue;
		}

		parent.replaceChild(fragment, textNode);
	}

	return container.innerHTML;
}

function Edit({ attributes, setAttributes }) {
	const { heading, content } = attributes;

	const onChangeHeading = useCallback(
		(nextValue) => setAttributes({ heading: nextValue }),
		[setAttributes]
	);

	const onChangeContent = useCallback(
		(nextValue) => setAttributes({ content: nextValue }),
		[setAttributes]
	);

	const blockProps = useBlockProps({ className: 'annotated-bibliography' });

	return (
		<section {...blockProps}>
			<RichText
				tagName="h2"
				className="bibliography-heading"
				value={heading}
				onChange={onChangeHeading}
				placeholder="Annotated Bibliography"
				allowedFormats={['core/bold', 'core/italic', 'core/link']}
			/>
			<RichText
				tagName="div"
				className="bibliography-list"
				value={content}
				onChange={onChangeContent}
				multiline="p"
				placeholder="Paste your bibliography here…"
				allowedFormats={['core/bold', 'core/italic', 'core/link']}
			/>
		</section>
	);
}

function Save({ attributes }) {
	const { heading, content } = attributes;
	const blockProps = useBlockProps.save({ className: 'annotated-bibliography' });
	const linkedContent = linkifyHtmlPreservingAnchors(content);

	return (
		<section {...blockProps}>
			<RichText.Content
				tagName="h2"
				className="bibliography-heading"
				value={heading}
			/>
			<RichText.Content
				tagName="div"
				className="bibliography-list"
				value={linkedContent}
			/>
		</section>
	);
}

registerBlockType(metadata.name, {
	...metadata,
	edit: Edit,
	save: Save,
});
