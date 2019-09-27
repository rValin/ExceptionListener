<template>
    <span :class="itemClass">{{ statusName }}</span>
</template>

<script>
    export default {
        name: 'Status',
        props: {
            status: String,
        },
        computed: {
            itemClass() {
                switch (this.status) {
                    case 'resolved':
                        return 'text-success';
                    case 'ignored':
                    case 'deleted':
                        return 'text-muted';
                    case 'regression':
                        return 'text-danger';
                    case 'open':
                    default:
                        return 'text-warning';
                }
            },
            statusName() {
                switch (this.status) {
                    case 'resolved':
                        return 'Résolu';
                    case 'ignored':
                        return 'Ignoré';
                    case 'deleted':
                        return 'Supprimé';
                    case 'regression':
                        return 'Regression';
                    case 'open':
                    default:
                        return 'Ouvert';
                }
            }
        }
    }
</script>