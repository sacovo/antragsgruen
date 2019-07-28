define(["require","exports","../shared/AntragsgruenEditor"],function(t,e,r){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var o=4,s=6,d=17,c=8,l=9,h=function(){function a(){}return a.init=function(t){console.log("Init statuses"),a.statuses=t,Object.keys(t).forEach(function(t){a.statusListeners[t]=[]})},a.getAmendmentStatus=function(t){return a.statuses[t]},a.registerParagraph=function(t,e){a.statusListeners[t].push(e)},a.setStatus=function(e,n){a.statuses[e]=n,a.statusListeners[e].forEach(function(t){t.onAmendmentStatusChanged(e,n)})},a.getAll=function(){return a.statuses},a.statusListeners={},a}(),a=function(){function i(){}return i.removeEmptyParagraphs=function(){$(".paragraphHolder").each(function(t,e){0==e.childNodes.length&&$(e).remove()})},i.accept=function(t,e){void 0===e&&(e=null);var n=$(t);n.hasClass("ice-ins")&&i.insertAccept(t,e),n.hasClass("ice-del")&&i.deleteAccept(t,e)},i.reject=function(t,e){void 0===e&&(e=null);var n=$(t);n.hasClass("ice-ins")&&i.insertReject(n,e),n.hasClass("ice-del")&&i.deleteReject(n,e)},i.insertReject=function(t,e){void 0===e&&(e=null);var n,a=t[0].nodeName.toLowerCase();n="li"==a?t.parent():t,"ul"==a||"ol"==a||"li"==a||"blockquote"==a||"pre"==a||"p"==a?(n.css("overflow","hidden").height(n.height()),n.animate({height:"0"},250,function(){n.remove(),$(".collidingParagraph:empty").remove(),i.removeEmptyParagraphs(),e&&e()})):(n.remove(),e&&e())},i.insertAccept=function(t,e){void 0===e&&(e=null);var n=$(t);n.removeClass("ice-cts ice-ins appendHint moved"),n.removeAttr("data-moving-partner data-moving-partner-id data-moving-partner-paragraph data-moving-msg"),"ul"!=t.nodeName.toLowerCase()&&"ol"!=t.nodeName.toLowerCase()||n.children().removeClass("ice-cts").removeClass("ice-ins").removeClass("appendHint"),"li"==t.nodeName.toLowerCase()&&n.parent().removeClass("ice-cts").removeClass("ice-ins").removeClass("appendHint"),"ins"==t.nodeName.toLowerCase()&&n.replaceWith(n.html()),e&&e()},i.deleteReject=function(t,e){void 0===e&&(e=null),t.removeClass("ice-cts ice-del appendHint"),t.removeAttr("data-moving-partner data-moving-partner-id data-moving-partner-paragraph data-moving-msg");var n=t[0].nodeName.toLowerCase();"ul"!=n&&"ol"!=n||t.children().removeClass("ice-cts").removeClass("ice-del").removeClass("appendHint"),"li"==n&&t.parent().removeClass("ice-cts").removeClass("ice-del").removeClass("appendHint"),"del"==n&&t.replaceWith(t.html()),e&&e()},i.deleteAccept=function(t,e){void 0===e&&(e=null);var n,a=t.nodeName.toLowerCase();n="li"==a?$(t).parent():$(t),"ul"==a||"ol"==a||"li"==a||"blockquote"==a||"pre"==a||"p"==a?(n.css("overflow","hidden").height(n.height()),n.animate({height:"0"},250,function(){n.remove(),$(".collidingParagraph:empty").remove(),i.removeEmptyParagraphs(),e&&e()})):(n.remove(),e&&e())},i}();e.MotionMergeChangeActions=a;var i=function(){function t(i,r,o,t){this.$element=i,this.parent=t;var s=null,d=null;i.popover({container:"body",animation:!1,trigger:"manual",placement:function(t){var a=$(t);return a.data("element",i),window.setTimeout(function(){var t=a.width(),e=i.offset().top,n=i.height();null===s&&0<t&&(s=r-t/2,(d=o+10)<e+19&&(d=e+19),e+n<d&&(d=e+n)),a.css("left",s+"px"),a.css("top",d+"px")},1),"bottom"},html:!0,content:this.getContent.bind(this)}),i.popover("show"),i.find("> .popover").on("mousemove",function(t){t.stopPropagation()}),window.setTimeout(this.removePopupIfInactive.bind(this),1e3)}return t.prototype.getContent=function(){var t=this.$element,e=t.data("cid");null==e&&(e=t.parent().data("cid")),t.parents(".texteditor").first().find("[data-cid="+e+"]").addClass("hover");var n=$('<div><button type="button" class="accept btn btn-sm btn-default"></button><button type="button" class="reject btn btn-sm btn-default"></button><a href="#" class="btn btn-small btn-default opener" target="_blank"><span class="glyphicon glyphicon-new-window"></span></a><div class="initiator" style="font-size: 0.8em;"></div></div>');if(n.find(".opener").attr("href",t.data("link")).attr("title",__t("merge","title_open_in_blank")),n.find(".initiator").text(__t("merge","initiated_by")+": "+t.data("username")),t.hasClass("ice-ins"))n.find("button.accept").text(__t("merge","change_accept")).click(this.accept.bind(this)),n.find("button.reject").text(__t("merge","change_reject")).click(this.reject.bind(this));else if(t.hasClass("ice-del"))n.find("button.accept").text(__t("merge","change_accept")).click(this.accept.bind(this)),n.find("button.reject").text(__t("merge","change_reject")).click(this.reject.bind(this));else if("li"==t[0].nodeName.toLowerCase()){var a=t.parent();a.hasClass("ice-ins")?(n.find("button.accept").text(__t("merge","change_accept")).click(this.accept.bind(this)),n.find("button.reject").text(__t("merge","change_reject")).click(this.reject.bind(this))):a.hasClass("ice-del")?(n.find("button.accept").text(__t("merge","change_accept")).click(this.accept.bind(this)),n.find("button.reject").text(__t("merge","change_reject")).click(this.reject.bind(this))):console.log("unknown",a)}else console.log("unknown",t),alert("unknown");return n},t.prototype.removePopupIfInactive=function(){return this.$element.is(":hover")?window.setTimeout(this.removePopupIfInactive.bind(this),1e3):0<$("body").find(".popover:hover").length?window.setTimeout(this.removePopupIfInactive.bind(this),1e3):void this.destroy()},t.prototype.affectedChangesets=function(){var t=this.$element.data("cid");return null==t&&(t=this.$element.parent().data("cid")),this.$element.parents(".texteditor").find("[data-cid="+t+"]")},t.prototype.performActionWithUI=function(t){var e=window.scrollX,n=window.scrollY;this.parent.saveEditorSnapshot(),this.destroy(),t.call(this),this.parent.focusTextarea(),window.scrollTo(e,n)},t.prototype.accept=function(){var n=this;this.performActionWithUI(function(){n.affectedChangesets().each(function(t,e){a.accept(e,function(){n.parent.onChanged()})})})},t.prototype.reject=function(){var n=this;this.performActionWithUI(function(){n.affectedChangesets().each(function(t,e){a.reject(e,function(){n.parent.onChanged()})})})},t.prototype.destroy=function(){this.$element.popover("hide").popover("destroy");var t=this.$element.data("cid");null==t&&(t=this.$element.parent().data("cid"));var n=!1;this.$element.parents(".texteditor").first().find("[data-cid="+t+"]").each(function(t,e){$(e).is(":hover")&&(n=!0)}),n||this.$element.parents(".texteditor").first().find("[data-cid="+t+"]").removeClass("hover");try{$(".popover").each(function(t,e){var n=$(e);n.data("element").is(":hover")||(n.popover("hide").popover("destroy"),n.remove(),console.warn("Removed stale window: ",n))})}catch(t){}},t}(),p=function(){function t(t,e){var n=this;this.$holder=t,this.$changedIndicator=e,this.unchangedText=null,this.hasChanged=!1;var a=t.find(".texteditor"),i=new r.AntragsgruenEditor(a.attr("id"));this.texteditor=i.getEditor(),f.addSubmitListener(function(){t.find("textarea.raw").val(n.texteditor.getData()),t.find("textarea.consolidated").val(n.texteditor.getData())}),this.setText(this.texteditor.getData()),this.$holder.find(".acceptAllChanges").click(this.acceptAll.bind(this)),this.$holder.find(".rejectAllChanges").click(this.rejectAll.bind(this)),this.texteditor.on("change",this.onChanged.bind(this))}return t.prototype.prepareText=function(t){var e=$("<div>"+t+"</div>");e.find("ul.appendHint, ol.appendHint").each(function(t,e){var n=$(e),a=n.data("append-hint");n.find("> li").addClass("appendHint").attr("data-append-hint",a).attr("data-link",n.data("link")).attr("data-username",n.data("username")),n.removeClass("appendHint").removeData("append-hint")}),e.find(".moved .moved").removeClass("moved"),e.find(".moved").each(this.markupMovedParagraph.bind(this)),e.find(".hasCollisions").attr("data-collision-start-msg",__t("merge","colliding_start")).attr("data-collision-end-msg",__t("merge","colliding_end"));var n=e.html();this.texteditor.setData(n),this.unchangedText=this.normalizeHtml(this.texteditor.getData()),this.texteditor.fire("saveSnapshot"),this.onChanged()},t.prototype.markupMovedParagraph=function(t,e){var n,a=$(e),i=a.data("moving-partner-paragraph");n=(n=a.hasClass("inserted")?__t("std","moved_paragraph_from"):__t("std","moved_paragraph_to")).replace(/##PARA##/,i+1),"LI"===a[0].nodeName&&(a=a.parent()),a.attr("data-moving-msg",n)},t.prototype.initializeTooltips=function(){var n=this;this.$holder.on("mouseover",".appendHint",function(t){var e=$(t.currentTarget);0<e.parents(".paragraphWrapper").first().find(".amendmentStatus.open").length||(f.activePopup&&f.activePopup.destroy(),f.activePopup=new i(e,t.pageX,t.pageY,n))})},t.prototype.acceptAll=function(){this.texteditor.fire("saveSnapshot"),this.$holder.find(".collidingParagraph").each(function(t,e){var n=$(e);n.find(".collidingParagraphHead").remove(),n.replaceWith(n.children())}),this.$holder.find(".ice-ins").each(function(t,e){a.insertAccept(e)}),this.$holder.find(".ice-del").each(function(t,e){a.deleteAccept(e)})},t.prototype.rejectAll=function(){this.texteditor.fire("saveSnapshot"),this.$holder.find(".collidingParagraph").each(function(t,e){$(e).remove()}),this.$holder.find(".ice-ins").each(function(t,e){a.insertReject($(e))}),this.$holder.find(".ice-del").each(function(t,e){a.deleteReject($(e))})},t.prototype.saveEditorSnapshot=function(){this.texteditor.fire("saveSnapshot")},t.prototype.focusTextarea=function(){},t.prototype.getContent=function(){return this.texteditor.getData()},t.prototype.setText=function(t){this.prepareText(t),this.initializeTooltips()},t.prototype.normalizeHtml=function(e){var n={"&nbsp;":" ","&ndash;":"-","&auml;":"ä","&ouml;":"ö","&uuml;":"ü","&Auml;":"Ä","&Ouml;":"Ö","&Uuml;":"Ü","&szlig;":"ß","&bdquo;":"„","&ldquo;":"“","&bull;":"•","&sect;":"§","&eacute;":"é","&rsquo;":"’","&euro;":"€"};return Object.keys(n).forEach(function(t){e=e.replace(new RegExp(t,"g"),n[t])}),e.replace(/\s+</g,"<").replace(/>\s+/g,">").replace(/<[^>]*>/g,"")},t.prototype.onChanged=function(){this.normalizeHtml(this.texteditor.getData())===this.unchangedText?(this.$changedIndicator.addClass("unchanged"),this.hasChanged=!1):(this.$changedIndicator.removeClass("unchanged"),this.hasChanged=!0)},t.prototype.hasChanges=function(){return this.hasChanged},t}(),u=function(){function t(t){var n=this;this.$holder=t,this.sectionId=parseInt(t.data("sectionId")),this.paragraphId=parseInt(t.data("paragraphId"));var e=t.find(".wysiwyg-textarea"),a=t.find(".changedIndicator");this.textarea=new p(e,a),this.initButtons(),t.find(".amendmentStatus").each(function(t,e){h.registerParagraph($(e).data("amendment-id"),n)})}return t.prototype.initButtons=function(){var a=this;this.$holder.find(".toggleAmendment").click(function(t){var e=$(t.currentTarget).find(".amendmentActive"),n=function(){1===parseInt(e.val())?(e.val("0"),e.parents(".btn-group").find(".btn").addClass("btn-default").removeClass("btn-success")):(e.val("1"),e.parents(".btn-group").find(".btn").removeClass("btn-default").addClass("btn-success")),a.reloadText()};a.textarea.hasChanges()?bootbox.confirm(__t("merge","reloadParagraph"),function(t){t&&n()}):n()});var i=function(t){var e=parseInt(t.data("amendment-id")),n=h.getAmendmentStatus(e);t.find(".dropdown-menu .selected").removeClass("selected"),t.find(".dropdown-menu .status"+n).addClass("selected")};this.$holder.find(".btn-group.amendmentStatus").on("show.bs.dropdown",function(t){i($(t.currentTarget))}),this.$holder.find(".btn-group .setStatus").click(function(t){t.preventDefault();var e=$(t.currentTarget).parents(".btn-group"),n=parseInt(e.data("amendment-id"));h.setStatus(n,parseInt($(t.currentTarget).data("status"))),i(e)})},t.prototype.onAmendmentStatusChanged=function(t,e){if(this.textarea.hasChanges())console.log("Skipping, as there are changes");else{var n=this.$holder.find(".amendmentStatus[data-amendment-id="+t+"]"),a=n.find(".btn"),i=n.find("input.amendmentActive");-1!==[o,s,d,c,l].indexOf(e)?(i.val("1"),a.removeClass("btn-default").addClass("btn-success")):(i.val("0"),a.addClass("btn-default").removeClass("btn-success")),this.reloadText()}},t.prototype.reloadText=function(){var n=this,a=[];this.$holder.find(".amendmentActive[value='1']").each(function(t,e){a.push(parseInt($(e).data("amendment-id")))});var t=this.$holder.data("reload-url").replace("DUMMY",a.join(","));$.get(t,function(t){n.textarea.setText(t.text);var e="";t.collisions.forEach(function(t){e+=t}),n.$holder.find(".collisionsHolder").html(e),0<t.collisions.length?n.$holder.addClass("hasCollisions"):n.$holder.removeClass("hasCollisions")})},t.prototype.getDraftData=function(){var a={};return this.$holder.find(".amendmentStatus").each(function(t,e){var n=$(e);a[n.data("amendment-id")]=0<n.find(".btn-success").length}),{amendmentToggles:a,text:this.textarea.getContent()}},t}(),f=function(){function i(t){var a=this;this.paragraphs=[],i.$form=t,h.init(t.data("amendment-statuses")),$(".paragraphWrapper").each(function(t,e){var n=$(e);n.find(".wysiwyg-textarea").on("mousemove",function(t){i.currMouseX=t.offsetX}),a.paragraphs.push(new u(n))}),i.$form.on("submit",function(){$(window).off("beforeunload",i.onLeavePage)}),$(window).on("beforeunload",i.onLeavePage),this.initDraftSaving()}return i.onLeavePage=function(){return __t("std","leave_changed_page")},i.addSubmitListener=function(t){this.$form.submit(t)},i.prototype.setDraftDate=function(t){this.$draftSavingPanel.find(".lastSaved .none").hide();var e=$("html").attr("lang"),n=new Intl.DateTimeFormat(e,{year:"numeric",month:"numeric",day:"numeric",hour:"numeric",minute:"numeric",hour12:!1}).format(t);this.$draftSavingPanel.find(".lastSaved .value").text(n)},i.prototype.saveDraft=function(){var e=this,n={amendmentStatuses:h.getAll(),paragraphs:{}};this.paragraphs.forEach(function(t){n.paragraphs[t.sectionId+"_"+t.paragraphId]=t.getDraftData()});var a=this.$draftSavingPanel.find("input[name=public]").prop("checked");$.ajax({type:"POST",url:i.$form.data("draftSaving"),data:{public:a?1:0,data:JSON.stringify(n),_csrf:i.$form.find("> input[name=_csrf]").val()},success:function(t){t.success?(e.$draftSavingPanel.find(".savingError").addClass("hidden"),e.setDraftDate(new Date(t.date)),a?i.$form.find(".publicLink").removeClass("hidden"):i.$form.find(".publicLink").addClass("hidden")):(e.$draftSavingPanel.find(".savingError").removeClass("hidden"),e.$draftSavingPanel.find(".savingError .errorNetwork").addClass("hidden"),e.$draftSavingPanel.find(".savingError .errorHolder").text(t.error).removeClass("hidden"))},error:function(){e.$draftSavingPanel.find(".savingError").removeClass("hidden"),e.$draftSavingPanel.find(".savingError .errorNetwork").removeClass("hidden"),e.$draftSavingPanel.find(".savingError .errorHolder").text("").addClass("hidden")}})},i.prototype.initAutosavingDraft=function(){var t=this,e=this.$draftSavingPanel.find("input[name=autosave]");if(window.setInterval(function(){e.prop("checked")&&t.saveDraft()},5e3),localStorage){var n=localStorage.getItem("merging-draft-auto-save");null!==n&&e.prop("checked","1"==n)}e.change(function(){var t=e.prop("checked");localStorage&&localStorage.setItem("merging-draft-auto-save",t?"1":"0")}).trigger("change")},i.prototype.initDraftSaving=function(){if(this.$draftSavingPanel=i.$form.find("#draftSavingPanel"),this.$draftSavingPanel.find(".saveDraft").on("click",this.saveDraft.bind(this)),this.$draftSavingPanel.find("input[name=public]").on("change",this.saveDraft.bind(this)),this.initAutosavingDraft(),this.$draftSavingPanel.data("resumed-date")){var t=new Date(this.$draftSavingPanel.data("resumed-date"));this.setDraftDate(t)}$("#yii-debug-toolbar").remove()},i.activePopup=null,i.currMouseX=null,i}();e.MotionMergeAmendments=f});
//# sourceMappingURL=MotionMergeAmendments.js.map
