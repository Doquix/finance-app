<script setup>
import { onMounted, ref, computed } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import Preloader from '../../components/Preloader.vue';

const selectedStatus = ref();
const appointmentStatus = ref([]);
const getAppointmentStatus = () => {
    axios.get('/api/appointment-status')
        .then((response) => {
            appointmentStatus.value = response.data;
        })
};
const appointments = ref([]);
const loading = ref(false);
const expandableId = ref(null);

const getAppointments = (status) => {
    loading.value = true;
    selectedStatus.value = status;
    const params = {};
    if (status) {
        params.status = status;
    }
    axios.get('/api/appointments', {
        params: params,
    })
        .then((response) => {
            appointments.value = response.data;
            loading.value = false;
        })
};

const appointmentsCount = computed(() => {
    return appointmentStatus.value.map(status => status.count).reduce((acc, value) => acc + value, 0);
});

const updateAppointmentStatusCount = (id) => {
    const deletedAppointmentStatus = appointments.value.data.find(appointment => appointment.id === id).status.name;
    const statusToUpdate = appointmentStatus.value.find(status => status.name === deletedAppointmentStatus);
    statusToUpdate.count--;
};

const deleteAppointment = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(`/api/appointments/${id}`)
                .then((response) => {
                    updateAppointmentStatusCount(id);
                    appointments.value.data = appointments.value.data.filter(appointment => appointment.id !== id);
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    )
                });
        }
    })
};

const toggleExpandable = (id) => {
  expandableId.value = expandableId.value === id ? null : id;
};
onMounted(() => {
    getAppointments();
    getAppointmentStatus();
});
</script>
<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Requests</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Appointments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-11">
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <router-link to="/admin/appointments/create">
                                <button class="btn btn-primary"><i class="fa fa-plus-circle mr-1"></i> Add New
                                    Appointment</button>
                            </router-link>
                        </div>
                        <div class="mb-2">
<form action="simple-results.html">
<div class="input-group input-group-lg">
<input type="search" class="form-control form-control-lg" placeholder="Exit Pass Code" >
<div class="input-group-append">
<button type="submit" class="btn btn-lg btn-default">
<i class="fa fa-search"></i>
</button>
</div>
</div>
</form>
</div>
                        
                        <div class="btn-group">
                            <button @click="getAppointments()" type="button" class="btn"
                                :class="[typeof selectedStatus === 'undefined' ? 'btn-secondary' : 'btn-default']">
                                <span class="mr-1">All</span>
                                <span class="badge badge-pill badge-info">{{ appointmentsCount }}</span>
                            </button>

                            <button v-for="status in appointmentStatus" @click="getAppointments(status.value)"
                                type="button" class="btn"
                                :class="[selectedStatus === status.value ? 'btn-secondary' : 'btn-default']">
                                <span class="mr-1">{{ status.name }}</span>
                                <span class="badge badge-pill" :class="`badge-${status.color}`">{{ status.count
                                    }}</span>
                            </button>
                        </div>
                    </div>
                    <!-- <div class="card">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Requestor Name</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Options</th>
                                        <th scope="col">Options</th>
                                        <th scope="col">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(appointment, index) in appointments.data" :key="appointment.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ appointment.client.first_name }} {{ appointment.client.last_name }}</td>
                                        <td>{{ appointment.start_time }}</td>
                                        <td>{{ appointment.end_time }}</td>
                                        <td>
                                            <span class="badge" :class="`badge-${appointment.status.color}`">{{
                                    appointment.status.name }}</span>
                                        </td>
                                        <td>
                                            <router-link :to="`/admin/appointments/${appointment.id}/edit`">
                                                <i class="fa fa-edit mr-2"></i>
                                            </router-link>
                                            
                                            <a href="#" @click.prevent="deleteAppointment(appointment.id)">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> -->
                    <div class="card">
                    <div class="card-body">
<table class="table table-bordered table-hover">
<thead>
<tr>
<th>#</th>
<th>Requestor Name</th>
<th>Date</th>
<th>Status</th>
<th>Exit Pass Code</th>
<th>Transfer Location FROM</th>
<th>Transfer Location TO</th>
<th>Item Condition</th>
</tr>
</thead>
<tbody>
<tr v-for="(appointment, index) in appointments.data" data-widget="expandable-table" aria-expanded="true" :key="appointment.id">
  <td>{{ index + 1 }}</td>
  <td>{{ appointment.requestor_name }}</td>
  <td>{{ appointment.start_time }}</td>
  <td><span class="badge" :class="`badge-${appointment.status.color}`">{{ appointment.status.name }}</span></td>
  <td>{{ appointment.exit_pass_code }}</td>
  <td>{{ appointment.transfer_location_from }}</td>
  <td>{{ appointment.transfer_location_to }}</td>
  <td>{{ appointment.item_condition }}</td>
  <td>
                                            <router-link :to="`/admin/appointments/${appointment.id}/edit`">
                                                <i class="fa fa-edit mr-2"></i>
                                            </router-link>
                                            
                                            <a href="#" @click.prevent="deleteAppointment(appointment.id)">
                                                <i class="fa fa-trash text-danger"></i>
                                            </a>
                                        </td>
</tr>
<tr class="expandable-body d-none"  v-if="appointments.data" v-for="(appointment, index) in appointments.data.slice().reverse()" :key="appointment.id">
    <td colspan="8">
        <p style="display: none;">
            {{ appointment.description }}
        </p>
    </td>
</tr>

</tbody>
</table>
</div>
</div>
                </div>
            </div>
        </div>
    </div>

    <Preloader :loading="loading" />

</template>
