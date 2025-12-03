<template>
    <div>
        <div class="form-group">
            <label>{{ __('Prajete si synchronizovať všetky platby?') }}</label>
            <select class="form-control" name="sync_all">
                <option value="0">{{ __('Nie, len posledné obdobie.') }}</option>
                <option value="1">{{ __('Áno, synchronizovať všetky platby.') }}</option>
            </select>
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    props : ['model', 'data', 'request'],
}
</script>