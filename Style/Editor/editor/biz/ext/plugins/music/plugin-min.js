KISSY.Editor.add("xiami-music",function(f){var d=KISSY.Editor,e=f.htmlDataProcessor,g=e&&e.dataFilter;g&&g.addRules({elements:{object:function(a){var b=a.attributes,h=a.attributes.title,c;if(!(b.classid&&String(b.classid).toLowerCase())){for(b=0;b<a.children.length;b++){c=a.children[b];if(c.name=="embed"){if(!d.Utils.isFlashEmbed(c))break;if(/xiami\.com/i.test(c.attributes.src))return e.createFakeParserElement(a,"ke_xiami","xiami-music",true,{title:h})}}return null}for(b=0;b<a.children.length;b++){c=
a.children[b];if(c.name=="param"&&c.attributes.name.toLowerCase()=="movie")if(/xiami\.com/i.test(c.attributes.value))return e.createFakeParserElement(a,"ke_xiami","xiami-music",true,{title:h})}},embed:function(a){if(!d.Utils.isFlashEmbed(a))return null;if(/xiami\.com/i.test(a.attributes.src))return e.createFakeParserElement(a,"ke_xiami","xiami-music",true,{title:a.attributes.title})}}},4);f.addPlugin("xiami-music",function(){var a=f.addButton("xiami-music",{contentCls:"ke-toolbar-music",title:"\u63d2\u5165\u867e\u7c73\u97f3\u4e50",
mode:d.WYSIWYG_MODE,loading:true});d.use("xiami-music/support",function(){var b=new d.XiamiMusic(f);a.reload({offClick:function(){b.show()},destroy:function(){b.destroy()}})});this.destroy=function(){a.destroy()}})},{attach:false,requires:["fakeobjects"]});
