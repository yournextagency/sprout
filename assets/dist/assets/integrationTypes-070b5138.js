class r{constructor(e){this.adminTableId=e,this.container=document.querySelector(this.adminTableId),this.tableData=JSON.parse(this.container.dataset.tableData),this.initVueAdminTable()}initVueAdminTable(){let e=[{name:"labelHtml",title:Craft.t("sprout-module-forms","Name"),callback:function(t){return'<a class="cell-bold" href="'+t.url+'">'+t.name+"</a>"}},{name:"formType",title:Craft.t("sprout-module-forms","Form Type")}];new Craft.VueAdminTable({columns:e,container:this.adminTableId,deleteAction:"sprout-module-forms/form-integrations/delete",deleteConfirmationMessage:Craft.t("sprout-module-forms","Are you sure you want to delete the Form Integration Type “{name}”?"),deleteSuccessMessage:Craft.t("sprout-module-forms","Form integration deleted"),deleteFailMessage:Craft.t("sprout-module-forms","Unable to delete form integration type. Remove integration type from all forms before deleting."),emptyMessage:Craft.t("sprout-module-forms","No integration types exist yet."),minItems:1,padded:!0,reorderAction:"sprout-module-forms/form-integrations/reorder",reorderSuccessMessage:Craft.t("sprout-module-forms","Form integration types reordered."),reorderFailMessage:Craft.t("sprout-module-forms","Couldn’t reorder form integration types."),tableData:this.tableData})}}window.IntegrationTypesSettings=r;
//# sourceMappingURL=integrationTypes-070b5138.js.map
