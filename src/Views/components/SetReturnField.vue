<template>
    <div class="form-group invoices-field" v-if="row.type == 'return'">
        <label>{{ field.name }} <span class="required">*</span></label>

        <div class="input-group">
            <span data-toggle="tooltip" :title="row.return_number && row.return_id ? 'Faktúra bola nájdená' : 'Faktúra nebola nájdená'" class="input-group-addon" :style="statusStyle">FV-</span>
            <input :disabled="row.id" type="text" :value="row.return_number" @keyup="onChangeNumber" :placeholder="year + '...'" class="form-control">
            <input type="hidden" :name="field_key" :value="row.return_number ? row.return_id||'-' : ''" class="form-control">
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    props : ['field_key', 'field', 'row', 'model', 'history_changed'],

    data(){
        return {

        }
    },

    computed : {
        //Get input value
        value(){
            return this.field.value || this.field.default;
        },
        year(){
            return (new Date()).getFullYear();
        },
        statusStyle(){
            console.log('wuala', this.row.return_number);
            if ( ! this.row.return_number ) {
                return {};
            }

            var color = ! this.row.return_id ? '#cc0000' : 'green';

            return {
                color : 'white',
                borderColor: color,
                backgroundColor : color,
            }
        },
    },

    methods : {
        //Update input value
        onChange(e){
            this.field.value = e.target.value;
        },
        onChangeNumber: _.debounce(function(e){
            if ( this.row.id ) {
                return;
            }

            var value = e.target.value;

            this.$set(this.row, 'return_number', value);

            console.log('ujha', value, this.row.return_number);

            this.$root.$http.get('/admin/invoices/get-by-number?number='+value)
                .then(response => {
                    var invoice = response.data;

                    if ( ! invoice || typeof invoice != 'object' ) {
                        this.row.return_id = null;
                    }

                    else {
                        this.row.return_id = invoice.id;

                        this.setFields(invoice);
                    }
                });
        }, 100),
        setFields(invoice){
            var sync = ['client_id', 'email', 'company_name', 'company_id', 'company_tax_id', 'company_vat_id', 'city', 'street', 'zipcode', 'country'];

            //Clone params
            for ( var key in sync ) {
                this.row[sync[key]] = invoice[sync[key]];
            }
        },
    }
}
</script>