<template>
    <div class="fframe_app">
        <div class="ff_app_body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'FluentAuthApp',
    data() {
        return {

        }
    },
    created() {
        jQuery('.update-nag,.notice, #wpbody-content > .updated, #wpbody-content > .error').remove();
    },
    mounted() {
        jQuery('.fframe_handheld span').on('click', function () {
            jQuery('ul.fframe_menu').toggle('show');
        });
    }
}
</script>
