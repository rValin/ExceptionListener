<template>
    <div class="container-fluid" v-if="exception">
        <b-breadcrumb :items="[{text: 'Liste', to:{name: 'liste'}}, {text: 'Exception '+ exception.id, active: true}]"></b-breadcrumb>
        <div class="p-3 mb-2 mt-2">
            <div class="row bg-white p-2 pt-4">
                <div class="exception-title col-8">
                    <h1 class="text-muted h5">Doctrine\DBAL\Exception\DriverException</h1>
                    <div class="d-flex">

                        <b-dropdown id="dropdown-1" text="Dropdown Button" variant="link" :no-caret="true">
                            <template v-slot:button-content><critical-level :critical-level="exception.critical_level"></critical-level></template>
                            <b-dropdown-item v-on:click="updateCriticalLevel('critical')"><critical-level critical-level="critical"></critical-level> Danger</b-dropdown-item>
                            <b-dropdown-item v-on:click="updateCriticalLevel('normal')"><critical-level critical-level="normal"></critical-level> Normal</b-dropdown-item>
                            <b-dropdown-item v-on:click="updateCriticalLevel('low')"><critical-level critical-level="low"></critical-level> Faible</b-dropdown-item>
                        </b-dropdown>

                        <div class="text-dark h5">{{ exception.name }}</div>
                    </div>
                </div>
                <div class="col-1 exception-title-info text-center">
                    <div class="exception-title-info h5">Erreurs</div>
                    <div class="exception-title-info h4 text-primary">{{ nb_occurences }}</div>
                </div>
                <div class="col-1 exception-title-info text-center">
                    <div class="exception-title-info h5">Utilisateurs</div>
                    <div class="exception-title-info h4 text-primary">{{ nb_users }}</div>
                </div>
                <div class="col-1 exception-title-info text-center">
                    <div class="exception-title-info h5">Langage</div>
                    <div class="exception-title-info h4 text-primary">{{ exception.language }}</div>
                </div>
                <div class="col-1 exception-title-info text-center">
                    <div class="exception-title-info h5">Statut</div>
                    <div class="exception-title-info h4">
                        <b-dropdown id="dropdown-1" text="Dropdown Button" variant="link" :no-caret="true">
                            <template v-slot:button-content><status :status="exception.status"></status></template>
                            <b-dropdown-item v-on:click="updateStatus('open')"><status status="open"></status></b-dropdown-item>
                            <b-dropdown-item v-on:click="updateStatus('resolved')"><status status="resolved"></status></b-dropdown-item>
                            <b-dropdown-item v-on:click="updateStatus('ignored')"><status status="ignored"></status></b-dropdown-item>
                        </b-dropdown>
                    </div>
                </div>
            </div>
        </div>

        <b-card no-body>
            <b-tabs content-class="mt-3">
                <b-tab title="Exception" active>
                    <b-card-text class="p-3">
                        <div class="row">
                            <div class="col-4">
                                <p><strong>Dernières 24h</strong></p>
                                <vue-apex-charts v-if="Object.keys(nb_occurences_hours).length > 0" width="500" type="bar"
                                                 :options="{chart: {id: 'chart-hours'}, xaxis: {categories: Object.keys(nb_occurences_hours)}, chart: {toolbar: {show: false,}}}"
                                                 :series="[{name: 'Occurence', data: Object.values(nb_occurences_hours)}]">
                                </vue-apex-charts>
                            </div>
                            <div class="col-4">
                                <p><strong>Dernier mois</strong></p>
                                <vue-apex-charts v-if="Object.keys(nb_occurences_days).length > 0" width="500" type="bar"
                                                 :options="{chart: {id: 'chart-days'}, xaxis: {categories: Object.keys(nb_occurences_days)}, chart: {toolbar: {show: false,}}}"
                                                 :series="[{name: 'Occurence', data: Object.values(nb_occurences_days)}]">
                                </vue-apex-charts>
                            </div>

                            <div class="col-4">
                                <vue-apex-charts type=bar height=80 :options="getOptionsForBrowserGraph('Navigateurs')" :series="getSeriesForBrowserGraph(browser_stats.browser)" />
                                <vue-apex-charts type=bar height=80 :options="getOptionsForBrowserGraph('Nav - Versions')" :series="getSeriesForBrowserGraph(browser_stats.browser_version)" />
                                <vue-apex-charts type=bar height=80 :options="getOptionsForBrowserGraph('OS')" :series="getSeriesForBrowserGraph(browser_stats.os)" />
                                <vue-apex-charts type=bar height=80 :options="getOptionsForBrowserGraph('OS - Version')" :series="getSeriesForBrowserGraph(browser_stats.os_version)" />
                            </div>
                        </div>
                    </b-card-text>
                </b-tab>
                <b-tab title="Request">
                    <b-card-text>
                        <div class="data-container">
                            <data-item name="Utilisateur" :value="currentOccurence.user"></data-item>
                            <data-item name="app_version" :value="currentOccurence.app_version"></data-item>
                            <data-item name="browser" :value="currentOccurence.browser.browser.name + ' ' + currentOccurence.browser.browser.version.value"></data-item>
                            <data-item name="OS" :value="currentOccurence.browser.os.name + ' ' + currentOccurence.browser.os.version.alias +' ('+currentOccurence.browser.os.version.value+')'"></data-item>
                        </div>

                        <data-object v-if="!isEmptyObject(currentOccurence.extra.POST)" :values="currentOccurence.extra.POST" title="Post"></data-object>
                        <data-object v-if="!isEmptyObject(currentOccurence.extra.COOKIES)" :values="currentOccurence.extra.COOKIES" title="Cookies"></data-object>
                        <data-object v-if="!isEmptyObject(currentOccurence.extra.SESSION)" :values="currentOccurence.extra.SESSION" title="Session"></data-object>
                    </b-card-text>
                </b-tab>
                <b-tab title="Stack Trace">
                    <b-card-text>
                        <trace :trace="currentOccurence.trace"></trace>
                    </b-card-text>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
    import axios from 'axios';
    import Trace from "./Trace";
    import DataItem from "./DataItem";
    import DataObject from "./DataObject";
    import VueApexCharts from 'vue-apexcharts'
    import CriticalLevel from "./Elements/CriticalLevel";
    import Status from "./Elements/Status";

    export default {
        name: 'Exception',
        components: {Status, CriticalLevel, Trace, DataItem, DataObject, VueApexCharts},
        data() {
            return {
                exception: null,
                nb_users: 1,
                nb_occurences: 1,
                nb_occurences_days: {},
                nb_occurences_hours: {},
                currentOccurence: null,
                browser_stats: {},
            }
        },
        mounted() {
            this.loadException();
        },
        methods: {
            updateStatus(newStatus) {
                if (newStatus !== this.exception.status) {
                    this.update({status: newStatus}).then(() => {
                        this.exception.status = newStatus;
                    })
                }
            },
            updateCriticalLevel(newLevel) {
                if (newLevel !== this.exception.critical_level) {
                    this.update({criticalLevel: newLevel}).then(() => {
                        this.exception.critical_level = newLevel;
                    })
                }
            },
            update(data) {
                return new axios.post(EXCEPTION_URIS.update + '?id='+ this.$route.params.id, data)
                    .then(response => response.data)
            },
            loadException() {
                axios.get(EXCEPTION_URIS.exception + '?id='+ this.$route.params.id)
                    .then(result => result.data)
                    .then(result => {
                        this.exception = result.item;
                        this.currentOccurence = result.item.occurences[0];
                        this.nb_occurences = result.nb_occurences;
                        this.nb_occurences_days = result.nb_occurences_days;
                        this.nb_occurences_hours = result.nb_occurences_hours;
                        this.nb_users = result.nb_users;
                        this.browser_stats = result.browser_stats;
                    })
                ;
            },
            isEmptyObject(value) {
                if (!value || typeof value !== 'object') {
                    return true;
                }

                return JSON.stringify({}) === JSON.stringify(value) || JSON.stringify([]) === JSON.stringify(value);
            },

            getSeriesForBrowserGraph(data) {
                let series = [];

                for (let title in data) {
                    series.push({
                        name: title,
                        data: [data[title]]
                    });
                }

                return series;
            },
            getOptionsForBrowserGraph(name) {
                return {
                    chart: {
                        stacked: true,
                        stackType: '100%',
                        toolbar: {
                            show: false,
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                        },
                    },
                    stroke: {
                        width: 1,
                        colors: ['#fff']
                    },
                    yaxis: {
                        labels: {
                            minWidth: 90,
                            maxWidth: 90,
                        }
                    },
                    xaxis: {
                        categories: [name],
                        labels: {
                            show: false,
                        },
                        axisBorder: {
                            show:false,
                        },
                        axisTicks: {
                            show: false,
                        },
                    },
                    grid: {
                        padding: {
                            left: 0,
                            right: 0
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    legend: {
                        show: false,
                    },
                }
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .exception-title {
        margin-bottom: 15px;
    }

    .data-container {
        display: flex;
        flex-wrap: wrap;
    }
</style>
