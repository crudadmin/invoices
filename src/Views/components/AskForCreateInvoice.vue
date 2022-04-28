<template>
    <div>
        <div v-if="send == 0">
            <div class="form-group">
                <label>{{ __('Zaslať vygenerovanú faktúru na email?') }}</label>
                <select class="form-control" v-model="send">
                    <option value="0">{{ __('Nezasielať.') }}</option>
                    <option value="1">{{ __('Zaslať, prosím.') }}</option>
                </select>
            </div>
        </div>
        <div v-else>
            <div class="form-group">
                <label>Email</label>
                <input type="email" v-model="email" class="form-control">
            </div>

            <div class="form-group">
                <label>{{ __('Poznámka') }}</label>
                <textarea name="message" v-model="message" class="form-control" rows="3"></textarea>
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    props : ['model', 'row', 'rows', 'request'],

    data(){
        return {
            email : this.row.email,
            message : null,
            send : 0,
        }
    },

    ready(){
        this.setRequestData();
    },

    watch: {
        email(){ this.setRequestData() },
        message(){ this.setRequestData() },
        send(){ this.setRequestData() },
    },

    methods: {
        setRequestData(){
            this.request.email = this.email;
            this.request.message = this.message;
            this.request.send = this.send;
        },
    },
}
</script>