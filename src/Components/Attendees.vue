<template>
    <div class="event_home box_wrapper">
        <div class="box_narrow">
            <div class="box_header">
                <div class="box_head">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item style="font-size: 18px;" :to="{ name: 'dashboard' }">Dashboard
                        </el-breadcrumb-item>
                        <el-breadcrumb-item style="font-size: 18px;">Attendees</el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
                <div class="box_actions">
                    <el-button @click="dialogVisible = true" type="primary">Import Attendees</el-button>
                    <el-button @click="exportAttendees()" type="default">Export Attendees</el-button>
                </div>
            </div>
            <div class="box_body" style="margin-bottom: 20px; padding-bottom: 10px;">
                <div class="search_bar">
                    <el-row :gutter="20">
                        <el-col :md="12" :sm="12">
                            <el-input clearable size="large" @keyup.enter.native="fetchAttendees" v-model="search"
                                      placeholder="Search Attendee">
                                <template #append>
                                    <el-button :disabled="loading" v-loading="loading" @click="fetchAttendees"
                                               type="success">Search
                                    </el-button>
                                </template>
                            </el-input>
                        </el-col>
                        <el-col md="12" :sm="12">
                            <el-select @change="fetchAttendees" v-model="eventType"
                                       placeholder="Search by Event Checkin" clearable size="large">
                                <el-option v-for="type in eventTypes" :key="type.id" :label="type.title"
                                           :value="type.id"></el-option>
                            </el-select>
                        </el-col>
                    </el-row>
                </div>
                <el-table v-loading="loading" :data="attendees" border stripe>
                    <el-table-column type="expand">
                        <template #default="props">
                            <pre>{{ props.row }}</pre>
                        </template>
                    </el-table-column>
                    <el-table-column prop="attendee_uid" label="UID" width="90"></el-table-column>
                    <el-table-column prop="card_id" label="Card ID" width="90"></el-table-column>
                    <el-table-column :min-width="200" prop="first_name" label="Name">
                        <template #default="scope">
                            <span>{{ scope.row.first_name }} {{ scope.row.last_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :min-width="230" prop="email" label="Email"></el-table-column>
                    <el-table-column prop="ticket_type" label="Ticket Type" width="180"></el-table-column>
                    <el-table-column prop="attendee_type" label="Attendee Type" width="130"></el-table-column>
                    <el-table-column prop="counter" label="Counter" width="90"></el-table-column>
                    <el-table-column prop="tshirt_size" label="T-Shirt Size" width="120"></el-table-column>
                    <el-table-column prop="purchase_at" label="Purchase At" width="180"></el-table-column>
                    <el-table-column prop="last_modified_at" label="Last Modified At" width="180"></el-table-column>
                    <el-table-column prop="twitter_username" label="Twitter" width="120"></el-table-column>
                    <el-table-column prop="phone_number" label="Phone Number" width="150"></el-table-column>
                </el-table>

                <hr style="margin: 20px 0;"/>

                <pagination :pagination="pagination" @fetch="fetchAttendees"></pagination>
            </div>
        </div>

        <el-dialog title="Import Attendees" v-model="dialogVisible">
            <el-form enctype="multipart/form-data" label-position="top" id="csv_upload_form" ref="csv_form">
                <el-form-item label="Your CSV Should have following Columns">
                    <p>{{ columns.join(', ') }}</p>
                    <p><b>Required Columns:</b> attendee_uid, first_name, email. Only new attendee_uid rows will be
                        imported</p>
                </el-form-item>
                <el-form-item label="Upload Your Formatted CSV">
                    <input type="file" name="attendee_csv"/>
                </el-form-item>
                <el-form-item>
                    <label>
                        <input id="update_if_exist" type="checkbox" name="update_if_exist"> Update Attendee if Exist in
                        the database
                    </label>
                </el-form-item>
                <el-form-item>
                    <el-button :disabled="uploading" v-loading="uploading" type="success" @click="importAttendeeCsv()">
                        Import CSV
                    </el-button>
                    <el-button @click="dialogVisible = false">Cancel</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
import Pagination from '../Bits/Pagination.vue'

export default {
    name: 'Attendees',
    components: {
        Pagination
    },
    data() {
        return {
            attendees: [],
            pagination: {
                total: 0,
                per_page: 10,
                current_page: 1,
            },
            eventTypes: [],
            loading: false,
            dialogVisible: false,
            uploading: false,
            columns: [
                'attendee_uid',
                'card_id',
                'ticket_type',
                'attendee_type',
                'counter',
                'first_name',
                'last_name',
                'email',
                'purchase_at',
                'last_modified_at',
                'twitter_username',
                'tshirt_size',
                'phone_number',
                'id_printed',
                'buyer_name',
                'buyer_email',
                'country',
                'purchase_at',
                'last_modified_at'
            ],
            update_if_exist: 'no',
            search: '',
            eventType: ''
        }
    },
    methods: {
        importAttendeeCsv() {
            // get csv and upload to server
            this.uploading = true;
            let form = document.getElementById('csv_upload_form');
            let formData = new FormData(form);
            this.$rawRequest('attendees/import', formData)
                .then(response => {
                    this.fetchAttendees();
                    this.dialogVisible = false;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        fetchAttendees() {
            this.loading = true;
            this.$get('attendees', {
                per_page: this.pagination.per_page,
                page: this.pagination.current_page,
                search: this.search,
                event_id: this.eventType
            })
                .then(response => {
                    this.attendees = response.attendees;
                    this.pagination.total = parseInt(response.total);
                })
                .catch(error => {
                    this.$handleError(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchEvents() {
            this.loading = true;
            this.$get('events')
                .then(response => {
                    this.eventTypes = response.events;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        exportAttendees() {
            const args = {
                action: 'syl_event_attendee_export',
                search: this.search,
                event_id: this.eventType
            };

            let url = this.appVars.ajax_url;
            // append args as query parameters to url
            url += '?' + Object.keys(args).map(key => {
                return encodeURIComponent(key) + '=' + encodeURIComponent(args[key]);
            }).join('&');

            window.open(url, '_blank');
        }
    },
    mounted() {
        this.fetchAttendees();
        this.fetchEvents();
    }
}
</script>
