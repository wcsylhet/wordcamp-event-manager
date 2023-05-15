<template>
    <div>
        <wp-event-type-page />
    </div>
</template>

<script type="text/babel">
import EventTypesPage from './Parts/EventTypesPage.vue'
export default {
    name: 'Administrator',
    components: {
        'wp-event-type-page': EventTypesPage
    },
}
</script>
