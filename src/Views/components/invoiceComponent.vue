<template>
    <div></div>
</template>

<script type="text/javascript">
export default {
    props : ['model', 'row', 'rows'],

    ready(){
        /*
         * You can watch actual field value, or other form fields in row variable
         */
        this.$watch('row.id', function(value){
            this.$set('model.fields.type.removeFromForm', value ? true : false);
        });
    }
}
</script>