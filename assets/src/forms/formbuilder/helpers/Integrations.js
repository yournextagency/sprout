const Integrations = {

    get integrationsFormFieldMetadata() {

        let FormBuilder = window.FormBuilder;

        let fieldLayout = {};

        if (FormBuilder.tabs.length && !FormBuilder.tabs[0].fields.length) {
            return [];
        }

        let fields = [];

        for (const tab of FormBuilder.tabs) {

            for (const element of tab.fields) {

                let fieldData = FormBuilder.FieldLayoutHelper.getFormFieldAttributes(element);

                let field = {
                    name: fieldData.field.name,
                    handle: fieldData.field.handle,
                    type: fieldData.field.type,
                    uid: fieldData.field.uid,
                };

                fields.push(field);
            }
        }

        FormBuilder.fields = fields;

        return JSON.stringify(fields);
    },
};

export { Integrations };