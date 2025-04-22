<template>
    <div class="form-group">
        <label>{{ field.name }}</label>

        <input
            type="integer"
            step="any"
            :name="name"
            :value="value"
            @keyup="onChange"
            @change="reloadPrices"
            :placeholder="field.name"
            class="form-control"
            :readonly="field.isReadonly()"
            :disabled="field.isDisabled()"
        />
    </div>
</template>

<script type="text/javascript">
export default {
    props: ['field_key', 'field', 'row', 'model', 'name'],

    data() {
        return {};
    },

    mounted() {
        if (this.field_key.substr(-3) != 'vat') {
            this.$watch('row.vat', function (value) {
                this.reloadPrices(this.row.price || 0);
            });
        }
    },

    computed: {
        //Get input value
        value() {
            return this.field.value || this.field.default;
        },
    },

    methods: {
        //Update input value
        onChange(e) {
            this.field.value = e.target.value;
        },
        reloadPrices(e) {
            var value = typeof e === 'object' && e.target ? e.target.value : e,
                vat = parseFloat(this.row.vat || this.model.fields.vat.default) || 0;

            if (typeof value == 'string') value = value.replace(',', '.');

            if (this.field_key.substr(-3) == 'vat') {
                this.row.price = parseFloat((parseFloat(value) || 0) / (1 + vat / 100)).toFixed(2);
            } else {
                this.row.price_vat = parseFloat((parseFloat(value) || 0) * (1 + vat / 100)).toFixed(2);
            }
        },
    },
};
</script>
