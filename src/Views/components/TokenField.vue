<template>
    <div class="form-group">
        <label>{{ field.name }} <span class="required" v-if="field.isRequired()">*</span></label>
        <input type="password" v-model="field.value" class="form-control" :name="field.getKey()" placeholder="********">
    </div>
</template>

<script type="text/javascript">
export default {
    props : ['field', 'model'],
}
</script>