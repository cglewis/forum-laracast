<template>
    <div class="">
        <div class="" v-for="(reply, index) in items" :key="reply.id">
            <reply :data="reply" @deleted="remove(index)"></reply>
        </div>

        <paginator :dataSet="dataSet" @changed="fetch"></paginator>
        <p v-if="$parent.locked">
            This thread has been locked. No more replies are allowed.
        </p>
        <new-reply @created="add" v-else></new-reply>

    </div>
</template>

<script>

    import Reply from './Reply.vue';
    import NewReply from './NewReply.vue';
    import colletion from '../mixins/colletion';

    export default {

        components: { Reply, NewReply },

        mixins: [colletion],

        data() {
            return {
                dataSet: false
            }
        },

        created() {
            this.fetch();
        },

        methods: {

            fetch(page) {
                axios.get(this.url(page))
                .then(this.refresh);
            },

            url(page) {
                if (!page) {
                    let query = location.search.match(/page=(\d+)/);
                    page = query ? query[1] : 1;
                }
                return location.pathname + '/replies?page=' + page;
                //return '${location.pathname}/replies';
            },

            refresh({data}) {
                this.dataSet = data;
                this.items = data.data;

                window.scrollTo(0, 0);
            }

        }

    }

</script>
