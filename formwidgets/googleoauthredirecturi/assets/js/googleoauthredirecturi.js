/*
 * Clipboard copy field widget plugin.
 *
 * Data attributes:
 * - data-control="googleoauthredirecturi" - enables the plugin on an element
 *
 * JavaScript API:
 * $('div#someElement').googleoauthredirecturi({...})
 */
+function ($) {
	"use strict";
	var Base = $.wn.foundation.base,
		BaseProto = Base.prototype

	var GoogleOAuthRedirectURI = function (element, options) {
		this.$el = $(element)
		this.options = options

		this.$input = this.$el.find('[data-input]').first()
		this.$icon = this.$el.find('[data-icon]').first()
		this.$copy = this.$el.find('[data-copy]').first()

		$.wn.foundation.controlUtils.markDisposable(element)
		Base.call(this)
		this.init()
	}

	GoogleOAuthRedirectURI.DEFAULTS = {
		readOnly: true,
		disabled: false,
		eventHandler: null,
	}

	GoogleOAuthRedirectURI.prototype = Object.create(BaseProto)
	GoogleOAuthRedirectURI.prototype.constructor = GoogleOAuthRedirectURI

	GoogleOAuthRedirectURI.prototype.init = function () {
		this.$input.on('focus', this.proxy(this.onFocus))

		if (this.$copy.length) {
			this.$copy.on('click', this.proxy(this.onCopy))
		}
	}

	GoogleOAuthRedirectURI.prototype.dispose = function () {
		this.$input.off('focus', this.proxy(this.onFocus))

		if (this.$copy.length) {
			this.$copy.off('click', this.proxy(this.onCopy))
		}

		this.$input = this.$icon = null
		this.$el = null

		BaseProto.dispose.call(this)
	}

	GoogleOAuthRedirectURI.prototype.onFocus = function () {
		this.$input.select()

		return false
	}

	GoogleOAuthRedirectURI.prototype.onCopy = function () {
		navigator.clipboard.writeText(this.$input.val()).then(
			() => {
				/* clipboard successfully set */
			},
			() => {
				/* clipboard write failed */

				var that = this,
					deferred = $.Deferred()

				deferred.then(function () {
					that.$input.focus()
					that.$input.select()

					try {
						document.execCommand('copy')
					} catch (err) {
					}

					that.$input.blur()
				})

				deferred.resolve()
			}
		)
	}

	var old = $.fn.googleoauthredirecturi

	$.fn.googleoauthredirecturi = function (option) {
		var args = Array.prototype.slice.call(arguments, 1), result
		this.each(function () {
			var $this = $(this)
			var data = $this.data('oc.googleoauthredirecturi')
			var options = $.extend({}, GoogleOAuthRedirectURI.DEFAULTS, $this.data(), typeof option == 'object' && option)
			if (!data) $this.data('oc.googleoauthredirecturi', (data = new GoogleOAuthRedirectURI(this, options)))
			if (typeof option == 'string') result = data[option].apply(data, args)
			if (typeof result != 'undefined') return false
		})

		return result ? result : this
	}

	$.fn.googleoauthredirecturi.noConflict = function () {
		$.fn.googleoauthredirecturi = old
		return this
	}

	$(document).render(function () {
		$('[data-control="googleoauthredirecturi"]').googleoauthredirecturi()
	});

}(window.jQuery);
