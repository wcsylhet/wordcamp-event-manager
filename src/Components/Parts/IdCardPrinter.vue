<template>
    <div class="event_home box_wrapper">
        <div v-loading="fetchingEvent" class="box_narrow">
            <div class="box_header">
                <div class="box_head">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item style="font-size: 18px;" :to="{ name: 'dashboard' }">Dashboard
                        </el-breadcrumb-item>
                        <el-breadcrumb-item style="font-size: 18px;">Print ID Cards</el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
            </div>
            <div class="box_body" style="margin-bottom: 20px; padding-bottom: 10px;">
                <el-table v-loading="loading" :data="card_types">
                    <el-table-column width="150" label="Card Type">
                        <template #default="scope">
                            {{ scope.row.attendee_type }}
                        </template>
                    </el-table-column>
                    <el-table-column width="150" label="Print / Non-Print">
                        <template #default="scope">
                            {{ scope.row.printed_count }} / {{ scope.row.non_printed_count }}
                        </template>
                    </el-table-column>
                    <el-table-column label="Print">
                        <template #default="scope">
                            <el-button @click="goToPrintPage(scope.row.attendee_type, 'all')" type="default" size="small">Print All</el-button>
                            <el-button @click="goToPrintPage(scope.row.attendee_type, 'no')"  type="primary" size="small">Print Unprinted Cards</el-button>
                        </template>
                    </el-table-column>
                    <el-table-column label="Actions">
                        <template #default="scope">
                            <el-button @click="markPrintStatus(scope.row.attendee_type, 'yes')" type="default"
                                       size="small">Mark all as Printed
                            </el-button>
                            <el-button @click="markPrintStatus(scope.row.attendee_type, 'no')" type="danger"
                                       size="small">Mark all as Unprinted
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'IdCardPrinter',
    data() {
        return {
            card_types: [],
            loading: false
        }
    },
    methods: {
        fetchCardTypes() {
            this.loading = true;
            this.$get('attendees/card-types')
                .then(response => {
                    this.card_types = response.card_types;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        markPrintStatus(type, status) {
            this.loading = true;
            this.$post('attendees/mark-print-status', {type, status})
                .then(response => {
                    this.$notify.success(response.message);
                    this.fetchCardTypes();
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        goToPrintPage(attendee_type, print_status) {
            const url = this.appVars.site_url+'?sc_print_id='+attendee_type+'&sc_print_status='+print_status;
            // open this url in a new tab
            window.open(url, '_blank');
        }
    },
    mounted() {
        this.fetchCardTypes();
    }
}
</script>
