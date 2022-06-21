<template><div></div></template>

<script type="text/javascript">
export default {
    props : ['field', 'model'],

    watch: {
        'field.options'(options){
            if ( options.length == 1 ){
                this.field.value = options[0][0];
                this.field.default = options[0][0];
                this.model.hideFromForm('subject_id', true);
            }
        },
    },
}
</script>