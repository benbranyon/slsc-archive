(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-common"],{b132:function(e,t,s){"use strict";s.d(t,"a",function(){return f});var o,n=s("bd86"),i=s("7618"),r=(s("7f7f"),s("f499")),a=s.n(r),l=(s("28a5"),s("ac6a"),s("6762"),s("2fdb"),s("a481"),s("e814")),c=s.n(l),d=(s("6b54"),s("66cb")),h=s.n(d),u=s("2ef0"),g=s.n(u),p=s("4328"),m=s.n(p),b=s("561c"),f={methods:(o={set_default_val:function(e,t,s){void 0==e[t]&&this.$set(e,t,s)},update_head_css:g.a.debounce(function(e){var t=h()(this.shared.settings.document.settings.linkColor).darken().toString();this.shared.settings.document.settings.linkDarkerColor=t;var s="";""!=this.shared.settings.document.settings.bgImage&&(s="url('"+this.shared.settings.document.settings.bgImage+"')"),""!=this.shared.settings.document.settings.bgImage&&""!=this.shared.settings.document.settings.bgDimming&&(s="linear-gradient(0deg, rgba(0,0,0,0."+this.shared.settings.document.settings.bgDimming+"), rgba(0,0,0,0."+this.shared.settings.document.settings.bgDimming+")),url('"+this.shared.settings.document.settings.bgImage+"')"),""==s&&"g"==this.shared.settings.document.settings.bgStyle&&(s="linear"==this.shared.settings.document.settings.bgGradient.type?"linear-gradient("+this.shared.settings.document.settings.bgGradient.angle+"deg, "+this.shared.settings.document.settings.bgGradient.color1+" "+this.shared.settings.document.settings.bgGradient.color1location+"%, "+this.shared.settings.document.settings.bgGradient.color2+" "+this.shared.settings.document.settings.bgGradient.color2location+"%)":"radial-gradient(circle at "+this.shared.settings.document.settings.bgGradient.position+", "+this.shared.settings.document.settings.bgGradient.color1+" "+this.shared.settings.document.settings.bgGradient.color1location+"%, "+this.shared.settings.document.settings.bgGradient.color2+" "+this.shared.settings.document.settings.bgGradient.color2location+"%)");var o="#sp-page{color:"+this.shared.settings.document.settings.textColor+"} #sp-page .sp-header-tag-h1,#sp-page .sp-header-tag-h2,#sp-page .sp-header-tag-h3,#sp-page .sp-header-tag-h4,#sp-page .sp-header-tag-h5,#sp-page .sp-header-tag-h6{color:"+this.shared.settings.document.settings.headerColor+"}#sp-page h1,#sp-page h2,#sp-page h3,#sp-page h4,#sp-page h5,#sp-page h6{color:"+this.shared.settings.document.settings.headerColor+"; font-family:"+this.font_render(this.shared.settings.document.settings.headerFont)+";font-weight:"+this.font_variant_render(this.shared.settings.document.settings.headerFontVariant,"weight")+";font-style:"+this.font_variant_render(this.shared.settings.document.settings.headerFontVariant,"style")+"} #sp-page a{color:"+this.shared.settings.document.settings.linkColor+"} #sp-page a:hover{color:"+t+"}#sp-page .btn{background-color:"+this.shared.settings.document.settings.buttonColor+"}body{background-color:"+this.shared.settings.document.settings.bgColor+"; background-image:"+s+";}",n=this,i="",r=m.a.stringify({css:this.shared.settings.document.settings.customCss});n.axios.post(seedprod_get_namespaced_custom_css_url,r,{"Content-Type":"application/x-www-form-urlencoded;charset=UTF-8"}).then(function(e){i=e.data,jQuery("#tmp-custom-css-style").remove(),jQuery("head").append("<style id='tmp-custom-css-style' type='text/css'></style>"),jQuery("#tmp-custom-css-style").html(o+i)}),this.shared.settings.document.settings.headCss=o},100),highlight_option_target:function(e){this.shared.highlight_option_target=e},moving:function(e,t){},scroll:function(e){function t(t){return e.apply(this,arguments)}return t.toString=function(){return e.toString()},t}(function(e){var t=jQuery("#seedprod-builder-view").scrollTop();jQuery("#seedprod-builder-view").scrollTop(t+e),this.shared.stop||setTimeout(function(){scroll(e)},20)}),start_move:function(){this.shared.is_moving=!0},end_move:function(e){this.shared.is_moving=!1,this.shared.stop=!1},mousePosition:function(e){console.log("event",e)},width_height_render:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";return/^\d+$/.test(e)&&(e+="px"),e},text_shadow:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"#000000",s="",o="0,0,0",n=h()(t);return n.getLuminance(),1==e&&(s="1px 1px 0px rgba("+o+",0.5)"),2==e&&(s="1px 1px 3px rgba("+o+",0.5)"),3==e&&(s="2px 2px 4px rgba("+o+",0.4)"),4==e&&(s="3px 3px 6px rgba("+o+",0.3)"),5==e&&(s="3px 4px 12px rgba("+o+",0.3)"),6==e&&(s="5px 5px 20px rgba("+o+",0.3)"),s},divider_shadow:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t="";return 1==e&&(t="0 1px 1px rgba(0,0,0,0.2)"),2==e&&(t="0 2px 2px rgba(0,0,0,0.4)"),3==e&&(t="0 4px 4px rgba(0,0,0,0.4)"),4==e&&(t="0 6px 6px rgba(0,0,0,0.4)"),t},box_shadow:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t="";return 1==e&&(t="0 1px 2px 0 rgba(0, 0, 0, 0.5)"),2==e&&(t="0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.6)"),3==e&&(t="0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.6)"),4==e&&(t="0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.5)"),5==e&&(t="0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.4)"),6==e&&(t="0 25px 50px -12px rgba(0, 0, 0, 0.25)"),7==e&&(t="0 10px 6px -6px #777"),t},border_render:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"";return""==e&&(e="1px"),""==t&&(t="solid"),"#666666"==s&&(s="#666666"),e+"px "+t+" "+s},font_variant_render:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"weight",s="",o="";return""!=e&&("weight"==t&&(s=c()(e)),"style"==t&&(o=e.replace(/[0-9]/g,""),""==o&&(o="normal"))),"weight"==t?s:o},font_render:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";return""!=e&&!1===e.includes(",")&&(e="'"+e+"'"),e}},Object(n["a"])(o,"border_render",function(e,t,s){return""==e||0==e||""==s?"":e+"px "+t+" "+s}),Object(n["a"])(o,"padding_render",function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"";return""==e&&""==t&&""==s&&""==o?(e=10,s=10,o=10,t=10,e+"px "+t+"px "+s+"px "+o+"px "):(""==e&&(e=0),""==t&&(t=0),""==s&&(s=0),""==o&&(o=0),e+"px "+t+"px "+s+"px "+o+"px ")}),Object(n["a"])(o,"border_radius_render",function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"";return""==e&&""==t&&""==s&&""==o?(e=0,s=0,o=0,t=0,e+"px "+t+"px "+s+"px "+o+"px "):(""==e&&(e=0),""==t&&(t=0),""==s&&(s=0),""==o&&(o=0),e+"px "+t+"px "+s+"px "+o+"px ")}),Object(n["a"])(o,"align_render",function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"align",s="";return"align"==t&&("left"==e&&(s="left"),"right"==e&&(s="right"),"center"==e&&(s="center")),"width"==t&&(s="full"==this.block.settings.align?"100%":"auto"),s}),Object(n["a"])(o,"load_font",function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"";if(""!=e&&this.shared.setup_page_meta.googlefonts["Google Fonts"][e]){if(""!=t){var s={},o=!1;try{this.shared.setup_page_meta.googlefonts["Google Fonts"][e].variants.forEach(function(e){if(e.id==t)throw s})}catch(r){o=!0}!1===o&&(t="")}var n="https://fonts.googleapis.com/css?family="+e.split(" ").join("+")+":"+t+"&display=swap",i=document.createElement("link");i.rel="stylesheet",i.href=n,document.head.appendChild(i)}}),Object(n["a"])(o,"add_section",function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,s={id:this.uid(),type:"section",rows:[],settings:JSON.parse(a()(this.shared.block_templates.section))};this.shared.settings.document.sections.splice(t+1,0,s),this.focus_block(s.id,"bottom"),this.$router.push({name:"setup_block_options"})}),Object(n["a"])(o,"focus_block",function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"top",s=!0;"bottom"===t&&(s=!1),this.$nextTick(function(){var t=this.shared.code_container;if(void 0!==e&&null!==e)try{t.querySelector("#sp-"+e).scrollIntoView(s)}catch(o){}})}),Object(n["a"])(o,"add_row",function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,o={id:this.uid(),type:"row",colType:"1-col",cols:[],settings:JSON.parse(a()(this.shared.block_templates.row))};this.shared.settings.document.sections[t].rows.splice(s+1,0,o),this.focus_block(o.id,"bottom"),this.$router.push({name:"setup_block_options"})}),Object(n["a"])(o,"generate_cols",function(e){var t,s;"1-col"==e&&(t=1),"2-col"!=e&&"left-sidebar"!=e&&"right-sidebar"!=e||(t=2),"3-col"==e&&(t=3),"4-col"==e&&(t=4),"5-col"==e&&(t=5),"6-col"==e&&(t=6);var o=[];for(s=0;s<t;s++){var n={id:this.uid(),type:"col",blocks:[],settings:JSON.parse(a()(this.shared.block_templates.col))};("left-sidebar"==e&&0==s||"right-sidebar"==e&&1==s)&&(n.settings.colWidth=35),("left-sidebar"==e&&1==s||"right-sidebar"==e&&0==s)&&(n.settings.colWidth=65),o.push(n)}return o}),Object(n["a"])(o,"add_cols",function(e,t){var s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:null,n=this.generate_cols(e);this.shared.settings.document.sections[s].rows[o].cols=n}),Object(n["a"])(o,"goto",function(e){this.$route.name!==e&&this.$router.push({name:e})}),Object(n["a"])(o,"add_block",function(){this.$router.push({name:"setup_block_options"})}),Object(n["a"])(o,"duplicate_element",function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:null,n=arguments.length>4&&void 0!==arguments[4]?arguments[4]:null,i=JSON.parse(a()(e));if(null!==t&&null!==s&&null!==o&&null!==n)i.id=this.uid(),this.shared.settings.document.sections[t].rows[s].cols[o].blocks.splice(n+1,0,i);else if(null!==t&&null!==s){var r=this;i.id=this.uid(),i.cols.forEach(function(e){e.id=r.uid(),e.blocks.forEach(function(e){e.id=r.uid()})}),this.shared.settings.document.sections[t].rows.splice(s+1,0,i)}else if(null!==t){var l=this;i.id=this.uid(),i.rows.forEach(function(e){e.id=l.uid(),e.cols.forEach(function(e){e.id=l.uid(),e.blocks.forEach(function(e){e.id=l.uid()})})}),this.shared.settings.document.sections.splice(t+1,0,i)}this.focus_block(i.id),this.edit_block(i.id,null,!0)}),Object(n["a"])(o,"delete_blank_element",function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;arguments.length>2&&void 0!==arguments[2]&&arguments[2],arguments.length>3&&void 0!==arguments[3]&&arguments[3];null!==e&&null!==t?this.$delete(this.shared.settings.document.sections[e].rows,t):null!==e&&this.$delete(this.shared.settings.document.sections,e)}),Object(n["a"])(o,"delete_element",function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,s=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,o=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,n=arguments.length>3&&void 0!==arguments[3]?arguments[3]:null;this.$swal({imageUrl:this.shared.plugin_path+"public/img/delete.png",title:Object(b["a"])("Are you sure you want to delete?","coming-soon"),type:null,showCancelButton:!0,confirmButtonColor:"#d33",confirmButtonText:Object(b["a"])("Yes, delete it!","coming-soon")}).then(function(i){i.value&&(null!==t&&null!==s&&null!==o&&null!==n?e.$delete(e.shared.settings.document.sections[t].rows[s].cols[o].blocks,n):null!==t&&null!==s?1==e.shared.settings.document.sections[t].rows.length?e.shared.settings.document.sections[t].rows[0].cols=[]:e.$delete(e.shared.settings.document.sections[t].rows,s):null!==t&&e.$delete(e.shared.settings.document.sections,t),e.$router.push({name:"setup_block_options"}),e.$swal({imageUrl:e.shared.plugin_path+"public/svg/success-24px-white.svg",text:"Deleted",toast:!0,type:null,customClass:"sp-toast-error",position:"top-end",showConfirmButton:!1,timer:3e3}))})}),Object(n["a"])(o,"edit_block",function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,s=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return(this.shared.highlight_option_target==e||!1!==s)&&(0!==t&&void(this.$route.params.blockid!=e&&this.$router.push({name:"setup_block_options",params:{id:this.shared.lpage.id,blockid:e}})))}),Object(n["a"])(o,"uid",function(){var e=String.fromCharCode(97+Math.floor(26*Math.random()))+Math.random().toString(36).substring(2,7).toLowerCase();return e}),Object(n["a"])(o,"help_iframe",function(e){var t=e.split("#"),s="";t[1]&&(e=t[0],s=t[1]),this.$swal({width:600,html:"<iframe class='iframe_loading' id='inline-help' src='https://staging.seedprod.com/docs/"+e+"?iframe=1&hash="+s+"' style='width:100%;' onload='help_iframe()'></iframe>",toast:!1,showCancelButton:!0,cancelButtonText:"Close",confirmButtonText:Object(b["a"])('Visit Docs&nbsp;<i class="fas fa-external-link-alt"></i>',"coming-soon")}).then(function(t){if(t.value){var o="https://staging.seedprod.com/docs/"+e+"#"+s;window.open(o,"_blank")}})}),Object(n["a"])(o,"show_upgrade_notice",function(e,t){var s=this,o=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"link",n=arguments.length>3&&void 0!==arguments[3]&&arguments[3],i=this;n?(e=Object(b["a"])("Upgrade to PRO","coming-soon"),t=Object(b["a"])("Increase traffic, engagement, and get more email subscribers. Click below to learn more about all our awesome features.","coming-soon")):(e+=Object(b["a"])(" is a PRO Feature","coming-soon"),t=Object(b["a"])("We're sorry, the ","coming-soon")+t+Object(b["a"])(" feature is not available on your plan. Please upgrade to the PRO plan to unlock all these awesome features.","coming-soon")),this.$swal({customContainerClass:"seedprod-upgrade-popup",imageUrl:i.shared.plugin_path+"public/img/lock.svg",title:e,text:t,type:null,showCancelButton:!1,confirmButtonColor:"#4CAF50",cancelButtonColor:"#d33",confirmButtonText:Object(b["a"])("UPGRADE TO PRO","coming-soon"),showCloseButton:!0,footer:'<i class="fas fa-check-circle"></i><div style="text-align:center;margin-left:40px;margin-right:40px;">'+Object(b["a"])("<strong>Bonus:</strong>&nbsp;SeedProd Lite users get a discount off the regular price, automatically applied at checkout.","coming-soon")+"</div>"}).then(function(e){e.value&&(window.open(s.shared.upgrade_link+o,"_blank"),s.$swal.fire({customContainerClass:"seedprod-moreinfo-popup",imageUrl:i.shared.plugin_path+"public/img/info-with-circle.svg",type:null,html:Object(b["a"])("Thanks for your interest in SeedProd Pro!<br>If you have any questions or issues just <a href='https://www.seedprod.com/?contact=1' target='_blank'>let us know</a>.<br><br>After purchasing SeedProd Pro, you'll need to download and install the Pro version of the plugin, and then remove the free plugin. <br><br>(Don't worry, all your settings will be preserved.)","coming-soon")}))})}),Object(n["a"])(o,"show_uplock_notice",function(e,t){arguments.length>2&&void 0!==arguments[2]&&arguments[2];var s=arguments.length>3&&void 0!==arguments[3]&&arguments[3],o=this;s?(e=Object(b["a"])("Upgrade to PRO","coming-soon"),t=Object(b["a"])("Increase traffic, engagement, and get more email subscribers. Click below to learn more about all our awesome features.","coming-soon")):(e=Object(b["a"])("Upgrade to Unlock ","coming-soon")+e,t=Object(b["a"])("We're sorry, the ","coming-soon")+t+Object(b["a"])(" feature is not available on your plan. Please upgrade your plan to unlock this feature and more!","coming-soon")),this.$swal({customContainerClass:"seedprod-upgrade-popup",imageUrl:o.shared.plugin_path+"public/img/lock.svg",title:e,text:t,type:null,showCancelButton:!1,confirmButtonColor:"#4CAF50",cancelButtonColor:"#d33",confirmButtonText:"UPGRADE",showCloseButton:!0,footer:Object(b["a"])("Upgrade with just a click of a button!","coming-soon")}).then(function(e){e.value&&window.open("https://app.seedprod.com/upgrade-license?license_key="+o.shared.license_key+"&api_token="+o.shared.api_token,"_blank")})}),Object(n["a"])(o,"debounce_update_mobile_css",g.a.debounce(function(e){jQuery("#tmp-custom-mobile-css-style").remove(),jQuery("head").append("<style id='tmp-custom-mobile-css-style' type='text/css'></style>"),jQuery("#tmp-custom-mobile-css-style").html(this.generate_mobile_css())},100)),Object(n["a"])(o,"update_mobile_css",function(){jQuery("#tmp-custom-mobile-css-style").remove(),jQuery("head").append("<style id='tmp-custom-mobile-css-style' type='text/css'></style>"),jQuery("#tmp-custom-mobile-css-style").html(this.generate_mobile_css())}),Object(n["a"])(o,"generate_mobile_css",function(){var e="",t=this.traverse(seedprod_store.settings.document,this.process);return t.forEach(function(t){var s=t.split(",");if(!1===lodash.isEmpty(s[2])){o=="sp-"+s[0]&&!1;var o="sp-"+s[0],n=lodash.kebabCase(s[1].replace("_mobile","")),i=s[2];e=e+".sp-mobile-view #"+o+".sp-css-target{",e="line-height"==n?e+n+":"+i+" !important;":e+n+":"+i+"px !important;",e+=" }",e=e+".sp-mobile-view #"+o+" .sp-css-target{",e="line-height"==n?e+n+":"+i+" !important;":e+n+":"+i+"px !important;",e+=" }"}}),this.shared.settings.document.settings.mobileCss=e,e}),Object(n["a"])(o,"process",function(e,t,s,o){-1!==e.indexOf("_mobile")&&!1===!!~o.indexOf(t)&&o.push(s+","+e+","+t)}),Object(n["a"])(o,"traverse",function(e,t){var s=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null,o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:[];for(var n in e)0===n.indexOf("id")&&(s=e[n]),t.apply(this,[n,e[n],s,o]),null!==e[n]&&"object"==Object(i["a"])(e[n])&&this.traverse(e[n],t,s,o);return o}),Object(n["a"])(o,"debounce_update_placeholder_css",g.a.debounce(function(e){jQuery("#tmp-custom-placeholder-css-style").remove(),jQuery("head").append("<style id='tmp-custom-placeholder-css-style' type='text/css'></style>"),jQuery("#tmp-custom-placeholder-css-style").html(this.generate_placeholder_css())},100)),Object(n["a"])(o,"update_placeholder_css",function(){jQuery("#tmp-custom-placeholder-css-style").remove(),jQuery("head").append("<style id='tmp-custom-placeholder-css-style' type='text/css'></style>"),jQuery("#tmp-custom-placeholder-css-style").html(this.generate_placeholder_css())}),Object(n["a"])(o,"generate_placeholder_css",function(){var e="",t=this.traverse(seedprod_store.settings.document,this.process_placeholder);return t.forEach(function(t){var s=t.split(",");if(!1===lodash.isEmpty(s[2])){o=="sp-"+s[0]&&!1;var o="sp-"+s[0],n=s[2],i=h()(n).setAlpha(.7);e=e+"input::placeholder, #"+o+" input::placeholder {",e=e+"color:"+i,e+=" }"}}),this.shared.settings.document.settings.placeholderCss=e,e}),Object(n["a"])(o,"process_placeholder",function(e,t,s,o){-1!==e.indexOf("fieldTextColor")&&!1===!!~o.indexOf(t)&&o.push(s+","+e+","+t)}),o)}}}]);