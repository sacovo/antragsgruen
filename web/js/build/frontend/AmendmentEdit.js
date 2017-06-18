define(["require","exports","../shared/AntragsgruenEditor","../shared/DraftSavingEngine"],function(t,a,e,i){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var n=function(){function t(a){this.$form=a,this.hasChanged=!1;var e=a.data("multi-paragraph-mode");if(void 0===e)throw"data-multi-paragraph-mode needs to be set";this.lang=$("html").attr("lang"),this.$form.find(".editorialChange input").change(this.editorialOpenerClicked.bind(this)).change(),this.initGlobalAlternative(),$(".input-group.date").datetimepicker({locale:this.lang,format:"L"}),e?this.initMultiParagraphMode():this.spmInit();var n=$("#draftHint"),r=n.data("motion-id"),o=n.data("amendment-id");new i.DraftSavingEngine(a,n,"motion_"+r+"_"+o),a.on("submit",function(){$(window).off("beforeunload",t.onLeavePage)})}return t.prototype.initGlobalAlternative=function(){},t.prototype.editorialOpenerClicked=function(){var t=this.$form.find("#sectionHolderEditorial"),a=t.find(".texteditor");if(this.$form.find(".editorialChange input").prop("checked")){if(t.removeClass("hidden"),void 0===CKEDITOR.instances.amendmentEditorial_wysiwyg){var i=new e.AntragsgruenEditor("amendmentEditorial_wysiwyg");a.parents("form").submit(function(){a.parent().find("textarea.raw").val(i.getEditor().getData())}),$("#"+a.attr("id")).on("keypress",this.onContentChanged.bind(this))}}else t.addClass("hidden")},t.prototype.initMultiParagraphMode=function(){var t=this;$(".wysiwyg-textarea:not(#sectionHolderEditorial)").each(function(a,i){var n=$(i).find(".texteditor"),r=new e.AntragsgruenEditor(n.attr("id")).getEditor();n.parents("form").submit(function(){n.parent().find("textarea.raw").val(r.getData()),void 0!==r.plugins.lite&&(r.plugins.lite.findPlugin(r).acceptAll(),n.parent().find("textarea.consolidated").val(r.getData()))}),$("#"+n.attr("id")).on("keypress",t.onContentChanged.bind(t))})},t.prototype.spmSetModifyable=function(){var t=this.$spmParagraphs.filter(".modified");0==t.length?this.$spmParagraphs.addClass("modifyable"):(this.$spmParagraphs.removeClass("modifyable"),$("input[name=modifiedParagraphNo]").val(t.data("paragraph-no")),$("input[name=modifiedSectionId]").val(t.parents(".texteditorBox").data("section-id")))},t.prototype.spmOnParaClick=function(t){var a=$(t.currentTarget);if(a.hasClass("modifyable")){a.addClass("modified"),this.spmSetModifyable();var i,n=a.find(".texteditor");i=void 0!==CKEDITOR.instances[n.attr("id")]?CKEDITOR.instances[n.attr("id")]:new e.AntragsgruenEditor(n.attr("id")).getEditor(),n.attr("contenteditable","true"),n.parents("form").submit(function(){n.parent().find("textarea.raw").val(i.getData()),void 0!==i.plugins.lite&&(i.plugins.lite.findPlugin(i).acceptAll(),n.parent().find("textarea.consolidated").val(i.getData()))}),$("#"+n.attr("id")).on("keypress",this.onContentChanged.bind(this)),n.focus()}},t.prototype.spmRevert=function(t){t.preventDefault(),t.stopPropagation();var a=$(t.target).parents(".wysiwyg-textarea"),i=a.find(".texteditor");i.attr("id");void 0!==CKEDITOR.instances[i.attr("id")]&&e.AntragsgruenEditor.destroyInstanceById(i.attr("id")),i.html(a.data("original")),a.removeClass("modified"),this.spmSetModifyable()},t.prototype.spmInitNonSingleParas=function(t,a){var i=$(a),n=i.find(".texteditor");if(!i.hasClass("hidden")){var r=new e.AntragsgruenEditor(n.attr("id")).getEditor();n.parents("form").submit(function(){n.parent().find("textarea.raw").val(r.getData()),void 0!==r.plugins.lite&&(r.plugins.lite.findPlugin(r).acceptAll(),n.parent().find("textarea.consolidated").val(r.getData()))}),$("#"+n.attr("id")).on("keypress",this.onContentChanged.bind(this))}},t.prototype.spmInit=function(){this.$spmParagraphs=$(".wysiwyg-textarea.single-paragraph"),this.$spmParagraphs.click(this.spmOnParaClick.bind(this)),this.$spmParagraphs.find(".modifiedActions .revert").click(this.spmRevert.bind(this)),this.spmSetModifyable(),$(".wysiwyg-textarea").filter(":not(.single-paragraph)").each(this.spmInitNonSingleParas.bind(this)),$(".texteditorBox").each(function(t,a){var e=$(a),i=e.data("section-id"),n=e.data("changed-para-no");n>-1&&$("#section_holder_"+i+"_"+n).click()})},t.onLeavePage=function(){return __t("std","leave_changed_page")},t.prototype.onContentChanged=function(){this.hasChanged||(this.hasChanged=!0,$("body").hasClass("testing")||$(window).on("beforeunload",t.onLeavePage))},t}();a.AmendmentEdit=n});
//# sourceMappingURL=AmendmentEdit.js.map
