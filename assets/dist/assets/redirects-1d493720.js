Craft.SproutRedirectElementIndex=Craft.BaseElementIndex.extend({init:function(e,t,n){this.on("selectSite",this.onChangeSite.bind(this)),this.base(e,t,n)},onChangeSite:function(){this.settings.context==="index"&&document.getElementById("sprout-redirects-new-button").setAttribute("href",this.getNewRedirectUrl())},getNewRedirectUrl:function(){const e="sprout/redirects/new",t=this.getSite(),n=t?{site:t.handle}:void 0;return Craft.getUrl(e,n)}});Craft.registerElementIndexClass("BarrelStrength\\Sprout\\redirects\\components\\elements\\RedirectElement",Craft.SproutRedirectElementIndex);window.SproutRedirectElementIndexInit();
//# sourceMappingURL=redirects-1d493720.js.map
