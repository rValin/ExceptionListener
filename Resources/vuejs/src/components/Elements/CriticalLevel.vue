<template>
    <span :class="'rounded-circle ' + itemClass"></span>
</template>

<script>
    export default {
        name: 'CriticalLevel',
        props: {
            criticalLevel: String,
        },
        computed: {
            itemClass() {
                switch (this.criticalLevel) {
                    case 'critical':
                        return 'bg-danger';
                    case 'low':
                        return 'bg-secondary';
                    case 'normal':
                    default:
                        return 'bg-warning';
                }
            },
            criticalLevelName() {
                return this.criticalLevel;
            }
        }
    }
</script>

<style scoped>
    .rounded-circle {
        display: inline-block;
        margin-right: 10px;
        width: 25px;
        min-width: 25px;
        height: 25px;
        align-self: center;
    }
</style>