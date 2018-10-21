define(["require","exports"],function(t,i){"use strict";Object.defineProperty(i,"__esModule",{value:!0});var e=function(){function t(t){this.otherInitiator=!1,this.wasPerson=!1,this.$editforms=t.parents("form").first(),this.$supporterData=t.find(".supporterData"),this.$initiatorData=t.find(".initiatorData"),this.$initiatorAdderRow=this.$initiatorData.find(".adderRow"),this.$fullTextHolder=$("#fullTextHolder"),this.$supporterAdderRow=this.$supporterData.find(".adderRow"),this.userData=t.data("user-data"),this.settings=t.data("settings"),this.$otherInitiator=t.find("input[name=otherInitiator]"),this.$otherInitiator.change(this.onChangeOtherInitiator.bind(this)).trigger("change"),t.find("#personTypeNatural, #personTypeOrga").on("click change",this.onChangePersonType.bind(this)).first().trigger("change"),this.$initiatorAdderRow.find("a").click(this.initiatorAddRow.bind(this)),this.$initiatorData.on("click",".initiatorRow .rowDeleter",this.initiatorDelRow.bind(this)),this.$supporterAdderRow.find("a").click(this.supporterAddRow.bind(this)),this.$supporterData.on("click",".supporterRow .rowDeleter",this.supporterDelRow.bind(this)),this.$supporterData.on("keydown"," .supporterRow input[type=text]",this.onKeyOnTextfield.bind(this)),$(".fullTextAdder a").click(this.fullTextAdderOpen.bind(this)),$(".fullTextAdd").click(this.fullTextAdd.bind(this)),0<this.$supporterData.length&&0<this.$supporterData.data("min-supporters")&&this.initMinSupporters(),this.$editforms.submit(this.submit.bind(this))}return t.prototype.onChangeOtherInitiator=function(){this.otherInitiator=1==this.$otherInitiator.val()||this.$otherInitiator.prop("checked"),this.onChangePersonType()},t.prototype.onChangePersonType=function(){$("#personTypeOrga").prop("checked")?(this.setFieldsVisibilityOrganization(),this.setFieldsReadonlyOrganization(),this.wasPerson&&this.$initiatorData.find("#initiatorPrimaryName").val(""),this.wasPerson=!1):(this.setFieldsVisibilityPerson(),this.setFieldsReadonlyPerson(),this.wasPerson=!0)},t.prototype.setFieldsVisibilityOrganization=function(){this.$initiatorData.addClass("type-organization").removeClass("type-person"),this.$initiatorData.find(".organizationRow").addClass("hidden"),this.$initiatorData.find(".contactNameRow").removeClass("hidden"),this.$initiatorData.find(".resolutionRow").removeClass("hidden"),this.$initiatorData.find(".genderRow").addClass("hidden"),this.$initiatorData.find(".adderRow").addClass("hidden"),$(".supporterData, .supporterDataHead").addClass("hidden")},t.prototype.setFieldsReadonlyOrganization=function(){this.$initiatorData.find("#initiatorPrimaryName").prop("readonly",!1),this.$initiatorData.find("#initiatorOrga").prop("readonly",!1)},t.prototype.setFieldsVisibilityPerson=function(){this.$initiatorData.removeClass("type-organization").addClass("type-person"),this.$initiatorData.find(".organizationRow").removeClass("hidden"),2==this.settings.contactName?(this.$initiatorData.find(".contactNameRow").removeClass("hidden"),this.$initiatorData.find(".contactNameRow input").prop("required",!0)):(1==this.settings.contactName?this.$initiatorData.find(".contactNameRow").removeClass("hidden"):this.$initiatorData.find(".contactNameRow").addClass("hidden"),this.$initiatorData.find(".contactNameRow input").prop("required",!1)),this.$initiatorData.find(".genderRow").removeClass("hidden"),this.$initiatorData.find(".resolutionRow").addClass("hidden"),this.$initiatorData.find(".adderRow").removeClass("hidden"),$(".supporterData, .supporterDataHead").removeClass("hidden")},t.prototype.setFieldsReadonlyPerson=function(){!this.userData.fixed||this.otherInitiator?(this.$initiatorData.find("#initiatorPrimaryName").prop("readonly",!1),this.$initiatorData.find("#initiatorOrga").prop("readonly",!1)):(this.$initiatorData.find("#initiatorPrimaryName").prop("readonly",!0).val(this.userData.person_name),this.$initiatorData.find("#initiatorOrga").prop("readonly",!0).val(this.userData.person_organization))},t.prototype.initiatorAddRow=function(t){t.preventDefault();var i=$($("#newInitiatorTemplate").data("html"));this.$initiatorAdderRow.before(i)},t.prototype.initiatorDelRow=function(t){t.preventDefault(),$(t.target).parents(".initiatorRow").remove()},t.prototype.supporterAddRow=function(t){t.preventDefault();var i=$($("#newSupporterTemplate").data("html"));this.$supporterAdderRow.before(i)},t.prototype.supporterDelRow=function(t){t.preventDefault(),$(t.target).parents(".supporterRow").remove()},t.prototype.initMinSupporters=function(){var i=this;this.$editforms.submit(function(t){if(!$("#personTypeOrga").prop("checked")){var e=0;i.$supporterData.find(".supporterRow").each(function(t,i){""!=$(i).find("input.name").val().trim()&&e++}),e<i.$supporterData.data("min-supporters")&&(t.preventDefault(),bootbox.alert(__t("std","min_x_supporter").replace(/%NUM%/,i.$supporterData.data("min-supporters"))))}})},t.prototype.fullTextAdderOpen=function(t){t.preventDefault(),$(t.target).parent().addClass("hidden"),$("#fullTextHolder").removeClass("hidden")},t.prototype.fullTextAdd=function(){for(var a=this,t=this.$fullTextHolder.find("textarea").val().split(";"),o=$("#newSupporterTemplate").data("html"),i=function(){for(var t=a.$supporterData.find(".supporterRow"),i=0;i<t.length;i++){var e=t.eq(i);if(""==e.find(".name").val()&&""==e.find(".organization").val())return e}var r=$(o);return 0<a.$supporterAdderRow.length?a.$supporterAdderRow.before(r):$(".fullTextAdder").before(r),r},e=null,r=0;r<t.length;r++)if(""!=t[r]){var n=i();if(null==e&&(e=n),0<n.find("input.organization").length){var s=t[r].split(",");n.find("input.name").val(s[0].trim()),1<s.length&&n.find("input.organization").val(s[1].trim())}else n.find("input.name").val(t[r])}this.$fullTextHolder.find("textarea").select().focus(),e.scrollintoview()},t.prototype.onKeyOnTextfield=function(t){var i;if(13==t.keyCode)if(t.preventDefault(),t.stopPropagation(),(i=$(t.target).parents(".supporterRow")).next().hasClass("adderRow")){var e=$($("#newSupporterTemplate").data("html"));this.$supporterAdderRow.before(e),e.find("input[type=text]").first().focus()}else i.next().find("input[type=text]").first().focus();else if(8==t.keyCode){if(""!=(i=$(t.target).parents(".supporterRow")).find("input.name").val())return;if(""!=i.find("input.organization").val())return;i.remove(),this.$supporterAdderRow.prev().find("input.name, input.organization").last().focus()}},t.prototype.submit=function(t){$("#personTypeOrga").prop("checked")&&2===this.settings.hasResolutionDate&&""===$("#resolutionDate").val()&&(t.preventDefault(),bootbox.alert(__t("std","missing_resolution_date"))),$("#personTypeNatural").prop("checked")&&2===this.settings.contactGender&&""===$("#initiatorGender input").val()&&(t.preventDefault(),bootbox.alert(__t("std","missing_gender")))},t}();i.InitiatorForm=e});
//# sourceMappingURL=InitiatorForm.js.map