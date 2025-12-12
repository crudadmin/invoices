<template>
    <div class="form-group" :class="{ disabled : disabled || readonly }">
        <div class="row">
            <div class="col-md-4">
                <label>{{ field.name }} <span class="required" v-if="required">*</span></label>
                <input
                    type="number"
                    step="any"
                    :name="field_key"
                    :value="valueOrDefault"
                    @keyup="changeNoVatPrice"
                    class="form-control"
                    :readonly="disabled || readonly">
            </div>
            <div class="col-md-4">
                <label>{{ __('DPH') }}</label>
                <input
                    type="number"
                    :step="inputStep"
                    :value="vatSize"
                    disabled
                    class="form-control">
            </div>
            <div class="col-md-4">
                <label>{{ __('Cena s DPH') }}</label>
                <input
                    type="number"
                    :step="inputStep"
                    :name="field_key+'_vat'"
                    :value="vatPrice"
                    @keyup="onPriceVatKeyUp($event); console.log('keyup', $event.target.value)"
                    @keyup.enter="onPriceVatChange($event); console.log('enter', $event.target.value)"
                    @change="onPriceVatChange($event); console.log('change', $event.target.value)"
                    class="form-control"
                    :readonly="disabled || readonly">
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    props : ['field_key', 'field', 'row', 'model', 'required', 'disabled', 'readonly'],

    data(){
        return {
            vat : 0,
        }
    },

    mounted(){
        //On vat value change
        this.onVatChange();

        //Bind default vat value
        this.changeVatValue(this.row[this.getVatFieldKey()]);
    },

    computed: {
        decimalSettings(){
            let defaultDecimals = parseInt((this.field.decimal_length||'').split(',')[1])||2,
                settings = {
                    round_without_vat : this.model.getSettings('decimals.round_without_vat', true),
                    places : this.model.getSettings('decimals.places', defaultDecimals),
                    rounding : this.model.getSettings('decimals.rounding', defaultDecimals),
                };

            //Get from store settings
            if ( this.getFreshModel('stores') ){
                return {
                    ...settings,
                    ...this.getFreshModel('stores').getSettings('decimals', {}),
                };
            }

            return settings;
        },
        valueOrDefault(){
            //We want rewrite value only if is initial null state
            if ( _.isNil(this.field.value) ) {
                return this.field.default||0;
            }

            return this.field.value;
        },
        inputStep(){
            return '.'+_.repeat(0, this.decimalSettings.places - 1)+'1';
        },
        vatSize(){
            return (this.field.value * (this.vat / 100)).toFixed(this.decimalSettings.places);
        },
        vatPrice(){
            return (this.field.value * (1 + (this.vat / 100))).toFixed(this.decimalSettings.places);
        },
        value(){
            return this.field.value || this.field.default || 0;
        },
    },

    methods: {
        changeNoVatPrice(e){
            this.field.setValue(e.target.value);
        },
        recalculateWithoutVatPrice(e){
            let rounding = this.decimalSettings.rounding;


            //If decimal rounding is disabled, we want save one more decimal here.
            if ( this.decimalSettings.round_without_vat === false ){
                rounding++;
            }

            this.field.value = _.round(e.target.value / (1 + (this.vat / 100)), rounding);
        },
        getFieldPrefix(){
            var fieldPrefix = this.field_key.split('_').slice(0, -1).join('_');

            return fieldPrefix ? fieldPrefix+'_' : '';
        },
        hasStaticFieldVat(){
            return this.model.fields[this.getFieldPrefix()+'vat'] ? true : false;
        },
        getVatFieldKey(){
            var field = this.getFieldPrefix()+(this.hasStaticFieldVat() ? 'vat' : 'vat_id');

            return this.model.fields[field] ? field : 'vat_id';
        },
        onVatChange(){
            this.model.fields[this.getVatFieldKey()].on('change', this.changeVatValue);
        },
        onPriceVatKeyUp(){
            this._onPriceVatKeyUp =_.debounce(function(e){
                this.recalculateWithoutVatPrice(e);
            }, 1500);
        },
        onPriceVatChange(e){
            // Cancel debounce
            this.$nextTick(() => {
                this._onPriceVatKeyUp?.cancel();
            });

            this.recalculateWithoutVatPrice(e);
        },
        changeVatValue(vatValue){
            if ( this.hasStaticFieldVat() ) {
                this.vat = vatValue;
            } else {
                var options = this.model.fields['vat_id'].options;

                for ( var i = 0; i < options.length; i++ ) {
                    if ( options[i][0] == vatValue ) {
                        this.vat = options[i][1].vat;
                    }
                }
            }
        }
    }
}
</script>