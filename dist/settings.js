(()=>{var e={16:()=>{jQuery(document).ready((function(e){"use strict";e((function(){e("#siteimprove-integration-token-request").on("click",(function(){e(this).prop("disabled",!0),e(this).parent().find(".spinner").remove(),e(this).parent().append('<span class="spinner is-active no-float"></span>'),e.post(ajaxurl,{action:"siteimproveRequestToken"},(function(t){var r=e("#siteimprove-integration-token-request");r.parent().find(".spinner").remove(),r.prop("disabled",!1),e("#siteimprove-integration-token").val(t)}))}))}))}))}},t={};function r(n){var o=t[n];if(void 0!==o)return o.exports;var i=t[n]={exports:{}};return e[n](i,i.exports,r),i.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";r(16)})()})();
//# sourceMappingURL=settings.js.map