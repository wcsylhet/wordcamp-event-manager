<template>
    <div class="dashboard box_wrapper">
        <div class="box_narrow">
            <div class="box_header">
                <div class="box_head">
                    <el-breadcrumb separator-class="el-icon-arrow-right">
                        <el-breadcrumb-item style="font-size: 18px;" :to="{ name: 'dashboard' }">Dashboard
                        </el-breadcrumb-item>
                        <el-breadcrumb-item style="font-size: 18px;">Event Types</el-breadcrumb-item>
                    </el-breadcrumb>
                </div>
                <div class="box_actions">
                    <el-button @click="dialogVisible = true" type="primary">Add Event Type</el-button>
                </div>
            </div>
            <div v-loading="loading" class="box_body">
                <el-table :data="event_types" stripe>
                    <el-table-column width="100px" label="ID" prop="id"></el-table-column>
                    <el-table-column label="Title" prop="title"></el-table-column>
                </el-table>
            </div>
        </div>
        <el-dialog title="Add Event Type" v-model="dialogVisible">
            <el-form :model="form" label-position="top"  ref="form">
                <el-form-item label="Event Type Title" prop="title">
                    <el-input v-model="form.title"></el-input>
                </el-form-item>
                <el-form-item label="Event Type Description" prop="description">
                    <el-input type="textarea" v-model="form.description"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button :disabled="creating" v-loading="creating" type="success" @click="createEventType()">Create Event Type</el-button>
                    <el-button @click="dialogVisible = false">Cancel</el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'EventTypes',
    data() {
        return {
            event_types: [],
            loading: false,
            dialogVisible: false,
            form: {
                title: '',
                description: ''
            },
            creating: false
        }
    },
    methods: {
        fetchEvents() {
            this.loading = true;
            this.$get('events')
                .then(response => {
                    this.event_types = response.events;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        createEventType() {
            this.creating = true;
            this.$post('events', this.form)
                .then(response => {
                    this.$notify.success(response.message);
                    this.fetchEvents();
                    this.form = {
                        title: '',
                        description: ''
                    };
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .finally(() => {
                    this.creating = false;
                    this.dialogVisible = false;
                });
        }
    },
    mounted() {
        this.fetchEvents();
    }
}
</script>
