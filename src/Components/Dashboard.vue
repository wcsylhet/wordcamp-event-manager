<template>
    <div class="dashboard box_wrapper">
        <div class="box_narrow">
            <div class="box_header">
                <div class="box_head">
                    <h3>Dashboard</h3>
                </div>
                <div class="box_actions">
                    <el-button type="primary" @click="fetchEvents">Refresh</el-button>
                </div>
            </div>
            <div v-if="!loading" class="box_body">
                <div style="font-size: 16px; margin-bottom: 20px;">
                    Hello {{ me.full_name }}, welcome to your WordCamp Event.
                </div>
                <h3>Select your Event Type</h3>

                <el-button v-for="event in events" size="large" :key="event.id" type="primary"
                           @click="goEventPage(event)">
                    {{ event.title }}
                </el-button>

                <el-button size="large" type="danger" @click="$router.push({name: 'all_check_in'})">Customized Checkin</el-button>

                <div>
                    <h3>Quick Stats</h3>
                    <el-table :data="events" stripe>
                        <el-table-column label="Event Type" prop="title"></el-table-column>
                        <el-table-column label="Checked In" prop="checked_in_count"></el-table-column>
                        <el-table-column label="% of Checked in">
                            <template #default="scope">
                                <span v-html="getPercent(scope.row)"></span>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>

                <div v-if="appVars.is_admin == 'yes'">
                    <h3>Other Actions</h3>
                    <ul class="listed_data">
                        <li>
                            <router-link :to="{ name: 'admin' }">Admin Panel</router-link>
                        </li>
                        <li>
                            <router-link :to="{ name: 'attendees' }">Attendees</router-link>
                        </li>
                        <li>
                            <router-link :to="{ name: 'prints' }">Print ID Cards</router-link>
                        </li>
                    </ul>
                </div>
            </div>
            <div v-else class="box_body">
                <el-skeleton :animated="true" :rows="5"></el-skeleton>
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
            this.$router.push({name: 'event_home', params: {id: eventItem.id}});
        },
        getPercent(eventItem) {
            if (eventItem.checked_in_count == 0) {
                return '0%';
            }
            // return percent to 2 decimal places
            return ((eventItem.checked_in_count / eventItem.total_attendees) * 100).toFixed(2) + '%';
        }
    },
    mounted() {
        this.fetchEvents();
    }
};
</script>
