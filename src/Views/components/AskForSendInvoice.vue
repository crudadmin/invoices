<template>
    <div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="email" class="form-control" placeholder="example@example.com">
        </div>

        <div class="form-group">
            <label>{{ __('Poznámka') }}</label>
            <textarea name="message" v-model="message" class="form-control" rows="3"></textarea>
        </div>
    </div>
</template>

<script type="text/javascript">
export default {
    props : ['model', 'request', 'data'],

    data(){
        return {
            email : this.data.email,
            message : this.data.message,
        }
    },

    mounted(){
        this.setRequestData();
    },

    watch: {
        email(){ this.setRequestData() },
        message(){ this.setRequestData() },
    },

    methods: {
        setRequestData(){
            this.request.email = this.email;
            this.request.message = this.message;
        },
    },
}
</script>