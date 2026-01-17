<!-- resources/js/Pages/Settings/Index.vue -->
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import InputText from "primevue/inputtext";
import TabPanel from "primevue/tabpanel";
import TabView from "primevue/tabview";
import Textarea from "primevue/textarea";

const props = defineProps({ settings: Object });

const form = useForm({
    settings: { ...props.settings },
});

const languages = [{ label: "English", value: "English" }];
const timezones = [
    { label: "(GMT/UTC 05:30)Dhaka", value: "(GMT/UTC 05:30)Dhaka" },
    { label: "(GMT/UTC 05:30)Kolkata", value: "(GMT/UTC 05:30)Kolkata" },
    { label: "(GMT/UTC 05:30)New Delhi", value: "(GMT/UTC 05:30)New Delhi" },
    { label: "(GMT/UTC 05:30)Tashkent", value: "(GMT/UTC 05:30)Tashkent" },
];
const dateFormats = [
    { label: "d-m-Y", value: "d-m-Y" },
    { label: "m/d/Y", value: "m/d/Y" },
    { label: "Y-m-d", value: "Y-m-d" },
];
const timeFormats = [
    { label: "24 Hours", value: "24 Hours" },
    { label: "12 Hours", value: "12 Hours" },
];
const drivers = [
    { label: "Twilio", value: "twilio" },
    { label: "Vonage", value: "vonage" },
    { label: "Disabled", value: "null" },
];

function save() {
    form.put(route("settings.update"));
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Settings" />
    <div class="p-6 max-w-4xl mx-auto">
      <TabView>
        <TabPanel header="General">
          <div class="grid gap-4">
            <div>
              <label>Application Name</label><InputText
                v-model="form.settings.general_application_name"
                class="w-full"
              />
            </div>
            <div>
              <label>Footer Text</label><Textarea
                v-model="form.settings.general_footer_text"
                rows="2"
                class="w-full"
              />
            </div>
            <div>
              <label>Language</label><Dropdown
                v-model="form.settings.general_language"
                class="w-full"
                :options="languages"
                option-label="label"
                option-value="value"
              />
            </div>
            <div>
              <label>Timezone</label><Dropdown
                v-model="form.settings.general_timezone"
                class="w-full"
                filter
                :options="timezones"
                option-label="label"
                option-value="value"
              />
            </div>
            <div>
              <label>Date Format</label><Dropdown
                v-model="form.settings.general_date_format"
                class="w-full"
                :options="dateFormats"
                option-label="label"
                option-value="value"
              />
            </div>
            <div>
              <label>Time Format</label><Dropdown
                v-model="form.settings.general_time_format"
                class="w-full"
                :options="timeFormats"
                option-label="label"
                option-value="value"
              />
            </div>
          </div>
        </TabPanel>

        <TabPanel header="Email (SMTP)">
          <div class="grid gap-4">
            <div>
              <label>Host</label><InputText
                v-model="form.settings.mail_host"
                class="w-full"
              />
            </div>
            <div>
              <label>Port</label><InputText
                v-model="form.settings.mail_port"
                class="w-full"
              />
            </div>
            <div>
              <label>Username</label><InputText
                v-model="form.settings.mail_username"
                class="w-full"
              />
            </div>
            <div>
              <label>Password</label><InputText
                v-model="form.settings.mail_password"
                type="password"
                placeholder="******"
                class="w-full"
              />
            </div>
            <div>
              <label>Encryption</label>
              <Dropdown
                v-model="form.settings.mail_encryption"
                class="w-full"
                :options="[
                  { label: 'TLS', value: 'tls' },
                  { label: 'SSL', value: 'ssl' },
                  { label: 'None', value: 'null' },
                ]"
                option-label="label"
                option-value="value"
              />
            </div>
            <div>
              <label>From Address</label><InputText
                v-model="form.settings.mail_from_address"
                class="w-full"
              />
            </div>
            <div>
              <label>From Name</label><InputText
                v-model="form.settings.mail_from_name"
                class="w-full"
              />
            </div>
          </div>
        </TabPanel>

        <TabPanel header="SMS">
          <div class="grid gap-4">
            <div>
              <label>Driver</label><Dropdown
                v-model="form.settings.sms_driver"
                class="w-full"
                :options="drivers"
                option-label="label"
                option-value="value"
              />
            </div>

            <template v-if="form.settings.sms_driver === 'twilio'">
              <div>
                <label>Twilio SID</label><InputText
                  v-model="form.settings.sms_twilio_sid"
                  class="w-full"
                />
              </div>
              <div>
                <label>Twilio Token</label><InputText
                  v-model="form.settings.sms_twilio_token"
                  type="password"
                  placeholder="******"
                  class="w-full"
                />
              </div>
              <div>
                <label>From</label><InputText
                  v-model="form.settings.sms_twilio_from"
                  class="w-full"
                />
              </div>
            </template>

            <template
              v-else-if="form.settings.sms_driver === 'vonage'"
            >
              <div>
                <label>API Key</label><InputText
                  v-model="form.settings.sms_vonage_api_key"
                  class="w-full"
                />
              </div>
              <div>
                <label>API Secret</label><InputText
                  v-model="
                    form.settings.sms_vonage_api_secret
                  "
                  type="password"
                  placeholder="******"
                  class="w-full"
                />
              </div>
              <div>
                <label>From</label><InputText
                  v-model="form.settings.sms_vonage_from"
                  class="w-full"
                />
              </div>
            </template>
          </div>
        </TabPanel>
      </TabView>

      <div class="mt-6">
        <Button
          :loading="form.processing"
          label="Save All"
          @click="save"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>
