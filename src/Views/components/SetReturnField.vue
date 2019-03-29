<template>
    <div class="form-group" v-if="row.type == 'return'">
        <label>{{ field.name }} <span class="required">*</span></label>

        <div class="input-group">
            <span data-toggle="tooltip" :title="row.return_number && row.return_id ? 'Faktúra bola nájdená' : 'Faktúra nebola nájdená'" class="input-group-addon" :style="statusStyle">FV-</span>
            <input :disabled="row.id" type="text" :name="return_number" :value="row.return_number" @keyup="onChangeNumber" :placeholder="year + '...'" class="form-control">
            <input type="hidden" :name="key" :value="row.return_number ? row.return_id||'-' : ''" class="form-control">
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    props : ['key', 'field', 'row', 'model', 'history_changed'],

    data(){
        return {

        }
    },

    ready(){

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
            if ( ! this.row.return_number )
                return {};

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
            if ( this.row.id )
                return;

            var value = e.target.value;

            this.$set('row.return_number', value);

            this.$root.$http.get('./invoices/get-by-number', { number : value })
                .then(response => {
                    var invoice = response.data;

                    if ( ! invoice || typeof invoice != 'object' )
                        this.$set('row.return_id', null);

                    else {
                        this.$set('row.return_id', invoice.id);

                        this.setFields(invoice);
                    }
                });
        }, 100),
        setFields(invoice){
            var sync = ['client_id', 'email', 'company_name', 'company_id', 'tax_id', 'vat_id', 'city', 'street', 'zipcode', 'country'];

            //Clone params
            for ( var key in sync )
                this.$set('row.'+sync[key], invoice[sync[key]]);
        },
    }
}
</script>