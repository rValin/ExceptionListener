<template>
    <main>
        <div class="container-fluid">
            <div class="p-3 mb-2 mt-2">
                <div class="row bg-white p-2 pt-4">
                    <div class="col-12"><h1 class="h3">Liste des exceptions</h1></div>
                </div>
                <form class="row bg-white pb-3">
                    <div class="col-8">
                        <input type="text" v-model="search" placeholder="Recherche" class="form-control" @input="refreshList">
                    </div>
                    <div class="col-2">
                        <multiselect
                                v-model="criticalLevels"
                                :options="dict.criticalLevels"
                                @input="refreshList"
                                :multiple="true"
                                trackBy="value"
                                label="label"
                                :limit="0"
                                :limitText="(count) => `${count} sélectionnés`"
                                deselectLabel="Désélectionné"
                                selectLabel="Sélectionné"
                                selectedLabel="Sélectionné">
                        </multiselect>
                    </div>
                    <div class="col-2">
                        <multiselect
                                v-model="statuts"
                                :options="dict.statuts"
                                @input="refreshList"
                                :multiple="true"
                                trackBy="value"
                                label="label"
                                :limit="0"
                                :limitText="(count) => `${count} sélectionnés`"
                                deselectLabel="Désélectionné"
                                selectLabel="Sélectionné"
                                selectedLabel="Sélectionné">
                        </multiselect>
                    </div>
                </form>
            </div>

        </div>
        <div class="container-fluid">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Erreur</th>
                    <th>Nombre d'occurences</th>
                    <th>Date d'apparition</th>
                    <th>Dernière occurence</th>
                    <th>Niveau critique</th>
                    <th>Statut</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>Erreur</th>
                    <th>Nombre d'occurences</th>
                    <th>Date d'apparition</th>
                    <th>Dernière occurence</th>
                    <th>Niveau critique</th>
                    <th>Statut</th>
                </tr>
                </tfoot>
                <tbody>
                <tr v-for="exception in exceptions" :key="exception.id">
                    <td><router-link :to="{ name: 'exception', params: {id: exception.id}}">{{ exception.name }}</router-link></td>
                    <td>{{ exception.occurences.length }}</td>
                    <td>{{ exception.first_saw_on }}</td>
                    <td>{{ exception.last_saw_on }}</td>
                    <td><critical-level :critical-level="exception.critical_level"></critical-level></td>
                    <td><status :status="exception.status"></status></td>
                </tr>
                </tbody>
            </table>
            <b-pagination
                    align="center"
                    v-model="currentPage"
                    :total-rows="nbItems"
                    :per-page="resultsPerPage"
                    @input="refreshList"
            ></b-pagination>
        </div>
    </main>

</template>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<script>
    import axios from 'axios';
    import Status from "./Elements/Status";
    import CriticalLevel from "./Elements/CriticalLevel";
    import Multiselect from 'vue-multiselect'

    export default {
        name: 'Liste',
        components: {CriticalLevel, Status, Multiselect},
        data() {
            return {
                exceptions: [],
                search: '',
                criticalLevels: [{value: 'critical', label: 'Critique'}, {value: 'normal', label: 'Normal'}, {value: 'low', label: 'Faible'}],
                statuts: [{value: 'open', label: 'Ouvert'}, {value: 'regression', label: 'Regression'}],
                dict: {
                    criticalLevels: [{value: 'critical', label: 'Critique'}, {value: 'normal', label: 'Normal'}, {value: 'low', label: 'Faible'}],
                    statuts: [{value: 'open', label: 'Ouvert'}, {value: 'resolved', label: 'Résolu'}, {value: 'ignored', label: 'Ignoré'}, {value: 'regression', label: 'Regression'}, {value: 'deleted', label: 'Supprimé'}],
                },
                nbItems: 0,
                currentPage: 1,
                resultsPerPage: 10,
                listToken: null,
            }
        },
        mounted() {
            this.refreshList();
        },
        methods: {
            refreshList() {
                if (this.listToken) {
                    this.listToken.cancel();
                }
                const CancelToken = axios.CancelToken;
                this.listToken = CancelToken.source();

                axios.post(EXCEPTION_URIS.list + '?page='+this.currentPage, {
                    criticalLevels: this.criticalLevels.map(criticalLevel => criticalLevel.value),
                    statuts: this.statuts.map(statut => statut.value),
                    search: this.search,
                }, {
                    cancelToken: this.listToken.token
                })
                    .then(result => result.data)
                    .then(result => {
                        this.exceptions = result.items;
                        this.nbItems = result.nb_items;
                    })
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
    .multiselect__single {
        display: none;
    }
</style>
