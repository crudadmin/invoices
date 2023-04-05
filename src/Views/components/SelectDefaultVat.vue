<script>
export default {
    props : ['field', 'model'],

    created(){
        this.setFieldDefaultVat();
    },

    watch : {
        getDefaultVat(value){
            this.setFieldDefaultVat();
        }
    },

    computed: {
        getDefaultVat(){
            let invoicesModel = this.model.getParentModel('invoices'),
                row = invoicesModel.getRow(),
                subject = invoicesModel.getOptionValue('subject_id', row.subject_id);

            if ( subject && subject.vat_default_value ){
                return parseFloat(subject.vat_default_value);
            }

            return this.model.getSettings('defaultVat');
        },
    },

    methods : {
        setFieldDefaultVat(){
            Vue.set(this.field, 'default', this.getDefaultVat);
        }
    }
}
</script>