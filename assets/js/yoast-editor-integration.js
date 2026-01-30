(function () {
	var config = window.global360blocksYoastEditorData || null;
	var wp = window.wp || {};
	var data = wp.data;
	var store = 'yoast-seo/editor';

	if (!config || !config.image || !config.image.url || !data || typeof data.dispatch !== 'function') {
		return;
	}

	function pushImage() {
		if (!data.dispatch || !data.select) {
			return false;
		}

		var dispatcher = data.dispatch(store);
		if (!dispatcher || typeof dispatcher.setContentImage !== 'function') {
			return false;
		}

		var desiredUrl = config.image.url;
		if (!desiredUrl) {
			return true;
		}

		try {
			var selector = data.select(store);
			if (selector && typeof selector.getContentImage === 'function') {
				var current = selector.getContentImage();
				if (current === desiredUrl) {
					return true;
				}
			}

			dispatcher.setContentImage(desiredUrl);
			return true;
		} catch (error) {
			return false;
		}
	}

	if (pushImage()) {
		return;
	}

	if (typeof data.subscribe !== 'function') {
		return;
	}

	var unsubscribe = data.subscribe(function () {
		if (pushImage() && typeof unsubscribe === 'function') {
			unsubscribe();
		}
	});
})();
