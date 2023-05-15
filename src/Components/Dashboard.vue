<template>
    <div class="dashboard box_wrapper">
        <div class="box_narrow">
            <div class="box_header">
                <div class="box_head">
                    <h3>Dashboard</h3>
                </div>
            </div>
            <div class="box_body">
                <div style="font-size: 16px; margin-bottom: 20px;">
                    Hello {{ me.full_name }}, welcome to your WordCamp Event.
                </div>
                <h3>Select your Event Type</h3>

                <el-skeleton v-if="loading" :animated="true" :rows="5"></el-skeleton>

                <el-button v-for="event in events" size="large" :key="event.id" type="primary" @click="goEventPage(event)">
                    {{ event.title }}
                </el-button>

                <h3>Other Actions</h3>
                <ul class="listed_data">
                    <li><router-link :to="{ name: 'admin' }">Admin Panel</router-link></li>
                    <li><router-link :to="{ name: 'attendees' }">Attendees</router-link></li>
                    <li><router-link :to="{ name: 'prints' }">Print ID Cards</router-link></li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'Dashboard',
    components: {},
    data() {
        return {
            me: this.appVars.me,
            events: [],
            loading: false
        }
    },
    methods: {
        fetchEvents() {
            this.loading = true;
            this.$get('events')
                .then(response => {
                    this.events = response.events;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        goEventPage(eventItem) {
            window.currentEventItem = eventItem;
            this.$router.push({ name: 'event_home', params: { id: eventItem.id } });
        }
    },
    mounted() {
        this.fetchEvents();
    }
};
</script>
