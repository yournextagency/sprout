class s{constructor(e){this.siteKey=e.siteKey,this.size=e.size??null,this.theme=e.theme??null,this.badge=e.badge??null,this.badgePosition=this.invisibleRecaptchaGetBadgePosition(this.badge),this.grecaptcha=e.grecaptcha,this.initRecaptchas()}initRecaptchas(){let e=this,i=document.querySelectorAll(".google-recaptcha-container");for(let t of i){let a=t.closest("form");this.addFormEventListener(a);let r=e.grecaptcha.render(t,{sitekey:e.siteKey,theme:e.theme,badge:e.badgePosition,size:"invisible",callback:()=>{e.sproutFormsSubmitEvent.detail.submitHandler.handleFormSubmit(),e.grecaptcha.reset()},"expired-callback":()=>{e.sproutFormsSubmitEvent.detail.submitHandler.onFormSubmitCancelledEvent(),e.grecaptcha.reset()}});a.setAttribute("data-google-recaptcha-widget-id",r);let n=a.querySelector('[type="submit"]'),l=a.querySelector(".google-recaptcha-inline-text-terms");this.badge==="inline-text"?(n.parentNode.insertBefore(l,n.nextSibling),l.style.display="block"):(n.parentNode.insertBefore(t,n.nextSibling),t.style.display="block")}}addFormEventListener(e){let i=this;e.addEventListener("onSproutFormsSubmit",function(t){let r=t.target.getAttribute("data-google-recaptcha-widget-id");i.sproutFormsSubmitEvent=t,i.grecaptcha.getResponse(r)||(t.preventDefault(),i.grecaptcha.execute(r))},!1)}invisibleRecaptchaGetBadgePosition(e){return["inline-badge","inline-text"].indexOf(e)>=0?"inline":e}}window.SproutFormsGoogleRecaptchaInvisible=s;
//# sourceMappingURL=reCaptchaInvisible-ddc145dc.js.map
