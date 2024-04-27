<script setup>
import axios from 'axios';
import { reactive, onMounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useToastr } from '@/toastr';
import { Form } from 'vee-validate';
import flatpickr from "flatpickr";
import 'flatpickr/dist/themes/light.css';

const router = useRouter();
const route = useRoute();
const toastr = useToastr();
const form = reactive({
    // title: '',
    // client_id: '',
    start_time: '',
    
    description: '',
    requestor_name: '',
    exit_pass_code: '',
    transfer_location_from: '',
    transfer_location_to: '',
    item_condition: '',
    
  
});

const handleSubmit = (values, actions) => {
    if (editMode.value) {
        editAppointment(values, actions);
    } else {
        createAppointment(values, actions);
    }
};

const createAppointment = (values, actions) => {
      // Generate a random exit passcode
    const exitPassCode = Math.random().toString(36).substring(2, 8).toUpperCase();
    
    // Assign the generated exit passcode to the form
    form.exit_pass_code = exitPassCode;
    axios.post('/api/appointments/create', form)
    .then((response) => {
        router.push('/admin/appointments');
        toastr.success('Appointment created successfully!');
    })
    .catch((error) => {
        actions.setErrors(error.response.data.errors);
    })
};

const editAppointment = (values, actions) => {
    axios.put(`/api/appointments/${route.params.id}/edit`, form)
    .then((response) => {
        router.push('/admin/appointments');
        toastr.success('Appointment updated successfully!');
    })
    .catch((error) => {
        actions.setErrors(error.response.data.errors);
    })
};

// const clients = ref();
// const getClients = () => {
//     axios.get('/api/clients')
//     .then((response) => {
//         clients.value = response.data;
//     })
// };

const getAppointment = () => {
    axios.get(`/api/appointments/${route.params.id}/edit`)
    .then(({data}) => {
        // form.title = data.title;
        form.requestor_name = data.requestor_name;
        form.start_time = data.formatted_start_time;
       
        form.description = data.description;
        form.exit_pass_code = data.exit_pass_code;
        form.transfer_location_from = data.transfer_location_from;
        form.transfer_location_to = data.transfer_location_to;
        form.item_condition = data.item_condition;
    })
};

const editMode = ref(false);

onMounted(() => {
    if (route.name === 'admin.appointments.edit') {
        editMode.value = true;
        getAppointment();
    }

    flatpickr(".flatpickr", {
        enableTime: true,
        dateFormat: "Y-m-d h:i K",
        defaultHour: 10,
    });
    // getClients();
});
</script>

<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <span v-if="editMode">Edit</span>
                        <span v-else>Create</span>
                        Appointment</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <router-link to="/admin/dashboard">Home</router-link>
                        </li>
                        <li class="breadcrumb-item">
                            <router-link to="/admin/appointments">Appointments</router-link>
                        </li>
                        <li class="breadcrumb-item active">
                            <span v-if="editMode">Edit</span>
                            <span v-else>Create</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <Form @submit="handleSubmit" v-slot:default="{ errors }">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="requestor_name">Requestor Name</label>
                                            <input v-model="form.requestor_name" type="text" class="form-control" :class="{ 'is-invalid': errors.requestor_name }" id="requestor_name" placeholder="Enter Name">
                                            <span class="invalid-feedback">{{ errors.requestor_name }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="item_condition">Item Status</label>
                                            <input v-model="form.item_condition" type="text" class="form-control" :class="{ 'is-invalid': errors.item_condition }" id="item_condition" placeholder="Enter Item Status">
                                            <span class="invalid-feedback">{{ errors.item_condition }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="transfer_location_from">Transfer From</label>
                                            <input v-model="form.transfer_location_from" type="text" class="form-control" :class="{ 'is-invalid': errors.transfer_location_from }" id="transfer_location_from" placeholder="Transfer From">
                                            <span class="invalid-feedback">{{ errors.transfer_location_from }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="transfer_location_to">Transfer To</label>
                                            <input v-model="form.transfer_location_to" type="text" class="form-control" :class="{ 'is-invalid': errors.transfer_location_to }" id="transfer_location_to" placeholder="Transfer To">
                                            <span class="invalid-feedback">{{ errors.transfer_location_to }}</span>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="client">Client Name</label>
                                            <select v-model="form.client_id" id="client" class="form-control" :class="{ 'is-invalid': errors.client_id }">
                                                <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.first_name }} {{ client.last_name }}</option>
                                            </select>
                                            <span class="invalid-feedback">{{ errors.client_id }}</span>
                                        </div>
                                    </div> -->
                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start-time">Start Time</label>
                                            <input v-model="form.start_time" type="text" class="form-control flatpickr" :class="{'is-invalid': errors.start_time}" id="start-time">
                                            <span class="invalid-feedback">{{ errors.start_time }}</span>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea v-model="form.description" class="form-control" :class="{'is-invalid': errors.description}" id="description" rows="3"
                                        placeholder="Enter Description"></textarea>
                                    <span class="invalid-feedback">{{ errors.description }}</span>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </Form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
