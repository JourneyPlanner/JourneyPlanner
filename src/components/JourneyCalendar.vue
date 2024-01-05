<script>
import {supabase} from "@/lib/supabaseClient";
import {useRoute} from "vue-router";
import {ref, toRaw} from "vue";
import FullCalendar from '@fullcalendar/vue3'
import interactionPlugin, {Draggable} from "@fullcalendar/interaction";
import dayGridPlugin from '@fullcalendar/daygrid';
import TimeGridPlugin from '@fullcalendar/timegrid';
import AddActivityIllustration from "@/components/illustrations/AddActivityIllustration.vue";
import momentTimezonePlugin from '@fullcalendar/moment-timezone';
import IconDelete from "@/components/icons/IconDelete.vue";


export default {
  beforeMount() {
    this.initializeJourneyID();
  },
  components: {
    IconDelete,
    AddActivityIllustration,
    FullCalendar
  },
  data() {
    return {
      currentUserRole: ref(),
      eventCount: ref(0),
      noEvents: ref(true),
      activities: ref(),
      showDataBool: false,
      nothing_To_Render: null,
      index: 0,
      test: {},
      name: ref(""),
      dauer: ref(""),
      oeffnungszeiten: ref(""),
      link: ref(""),
      kontakt: ref(""),
      adresse: ref(""),
      kosten: ref(""),
      beschreibung: ref(""),
      ausgewaehltesEvent: ref(""),
      startingDate: ref(""),
      INITIAL_EVENTS: [],
      calendarPlugins: [interactionPlugin, momentTimezonePlugin],
      calendarOptions: {
        plugins: [dayGridPlugin, interactionPlugin, TimeGridPlugin, momentTimezonePlugin],
        headerToolbar: {
          start: 'title prev,next today',
          center: '',
          end: 'dayGridYear dayGridMonth timeGridWeek timeGridDay'
        },
        initialView: 'timeGridWeek',
        initialDate: '',
        initialEvents: [],
        locale: "de",
        height: "auto",
        eventReceive: this.initializeDrop,
        eventDrop: this.initializeDrop,
        eventResize: this.initializeDrop,
        eventClick: this.showData,
        slotDuration: "01:00:00",
        allDaySlot: false,
        timeZone: 'local'
      }
    };
  },
  async mounted() {
    this.setupDraggable();
  },
  methods: {
    showData(info) {
      for (let i = 0; i < this.activities.length; i++) {
        if (this.activities[i].pk_activity_uuid === info.event.extendedProps.defId) {
          this.name = this.activities[i].name;
          this.dauer = this.activities[i].estimated_duration / 60 + "h";
          this.oeffnungszeiten = this.activities[i].opening_hours;
          this.link = this.activities[i].google_maps_link;
          this.kontakt = this.activities[i].contact;
          this.adresse = this.activities[i].address;
          this.kosten = this.activities[i].cost;
          this.beschreibung = this.activities[i].description;
          this.ausgewaehltesEvent = this.activities[i].pk_activity_uuid
        }
      }
      this.showDataBool = true;
    },
    setupDraggable() {
      new Draggable(document.getElementById("planned-tasks"), {
        itemSelector: ".fc-event"
      })
    },
    async initializeData() {
      this.calendarOptions.initialEvents = toRaw(this.INITIAL_EVENTS);
      this.calendarOptions.initialDate = this.startingDate;
    },
    async deleteFromCalendar(id) {
      const {error} = await supabase
          .from('activity')
          .update({
            added_to_calendar: false, cal_date_start: null, cal_date_end: null
            , cal_from: null, cal_to: null
          })
          .eq('pk_activity_uuid', id);
      if (error) {
        console.log(error);
      }
      this.showDataBool = false;
      location.reload();
    }, async initializeDrop(event) {
      const startTime = event.event._instance.range.start.toISOString().split("T");
      startTime[1] = startTime[1].substring(0, 8);
      const endTime = event.event._instance.range.end.toISOString().split("T");
      endTime[1] = endTime[1].substring(0, 8);

      const {error} = await supabase
          .from('activity')
          .update({
            added_to_calendar: true, cal_date_start: startTime[0], cal_date_end: endTime[0]
            , cal_from: startTime[1], cal_to: endTime[1]
          })
          .eq('pk_activity_uuid', event.event._def.extendedProps.defId);
      if (error) {
        console.log(error);
      }
      event.draggedEl.parentNode.removeChild(event.draggedEl);
      this.eventCount--;
      if (this.eventCount <= 0){
        this.noEvents = true;
      }
    },
    async initializeJourneyID() {
      const route = useRoute();
      this.journeyID = ref(route.params.uuid);
      await this.loadData();
    },
    async loadData() {
      await this.getUserRole();
      const {data, error} = await supabase
          .from('activity')
          .select(`
          pk_activity_uuid,
          estimated_duration,
          fk_journey_uuid,
          contact,
          address,
          cost,
          description,
          opening_hours,
          google_maps_link,
          name,
          added_to_calendar,
          cal_date_start,
          cal_date_end,
          cal_from,
          cal_to
        `)
          .eq('fk_journey_uuid', this.journeyID.value);

      if (error) {
        console.error(error);
        return;
      }

      const {data: data2, error: error2} = await supabase
          .from('journey')
          .select(`
          from,
          to
        `)
          .eq('pk_journey_uuid', this.journeyID.value);
      const currentDate = new Date();
      const journeyStartDate = new Date(data2[0].from);
      const journeyEndDate = new Date(data2[0].to);
      if (currentDate > journeyStartDate && currentDate < journeyEndDate) {
        this.startingDate = currentDate;
      } else {
        this.startingDate = data2[0].from;
      }

      if (error2) {
        console.error(error2);
        return;
      }

      if (data) {
        data.forEach((row) => {
          if (row.added_to_calendar) {
            if (this.currentUserRole === 1) {
              this.INITIAL_EVENTS[this.index] = {
                id: this.index,
                title: row.name,
                start: row.cal_date_start + 'T' + row.cal_from,
                end: row.cal_date_end + 'T' + row.cal_to,
                editable: true,
                timeZone: "local",
                defId: row.pk_activity_uuid
              };
            } else if (this.currentUserRole === 0) {
              this.INITIAL_EVENTS[this.index] = {
                id: this.index,
                title: row.name,
                start: row.cal_date_start + 'T' + row.cal_from,
                end: row.cal_date_end + 'T' + row.cal_to,
                editable: false,
                timeZone: "local",
                defId: row.pk_activity_uuid
              };
            }

            this.index++;
          } else {
            this.noEvents = false;
            this.eventCount++;
          }
        });
        if (this.INITIAL_EVENTS.length <= 0) {
          this.nothing_To_Render = true;
        }

        this.activities = data;
      }
      await this.initializeData();
    },
    async getUserRole() {
      const {data: {user}} = await supabase.auth.getUser();
      const {data, error} = await supabase
          .from('user_is_in')
          .select(`
          function
        `)
          .eq('pk_user_uuid', user.id)
          .eq('pk_journey_uuid', this.journeyID.value);

      if (error) {
        console.error(error);
      }
      this.currentUserRole = data[0].function;
      if (this.currentUserRole === 0) {
        document.getElementById("showDraggabeles").style.display = "none";
      }
    }
  },
  computed: {
    formatTime(decimalTime) {
      return (decimalTime) => {
        const hour = Math.floor(decimalTime);
        const minute = Math.round((decimalTime - hour) * 60);

        // Format the time as HH:mm
        return `${hour}:${minute < 10 ? '0' : ''}${minute}`;
      };
    },
    handleClose() {
      return () => {
        this.showDataBool = false;
      };
    }
  },
}
;


</script>

<template>
  <div>
    <section class="content mt-4">
      <div class="container-fluid">
        <Dialog :visible="showDataBool" :close-on-escape="true" :header="' '"
                :style="{ width: '50rem' }" @update:visible="handleClose()">
          <div class="relative flex flex-col justify-center items-center">
            <div class="flex flex-row justify-between">
              <h1 class="font-nunito text-xl font-bold text-text-black mr-2">{{ name }}</h1>
              <button v-if="currentUserRole === 1"
                      class="bg-delete rounded-3xl font-nunito text-base text-text-black font-bold py-1 px-2 shadow-md flex flex-row hover:opacity-80"
                      severity="danger"
                      type="button"
                      @click="deleteFromCalendar(ausgewaehltesEvent)">
                <IconDelete class="text-black"/>
                <span>Aus Plan entfernen</span>
              </button>
            </div>
            <div class="bg-primary rounded-[58px] pl-6 pt-3 pr-10 pb-6 mt-2 w-[60%]">
              <form class="flex flex-col font-nunito font-semibold text-xl text-text-black">
                <div class="flex flex-row gap-5 grid grid-cols-2">
                  <div>
                    <div class="flex flex-col">
                      <label for="journey-dauer" class="pt-2">Dauer</label>
                      <input disabled :placeholder=dauer
                             class="rounded border-none pl-1.5 placeholder-text-black bg-disabled-input">
                    </div>
                    <div class="flex flex-col">
                      <a :href=link>
                        <label for="journey-to" class="pt-2">Google-Maps</label>
                        <input disabled :value=link
                               class="w-[100%] rounded border-none bg-disabled-input pl-1.5 placeholder-text-black">
                      </a>
                    </div>
                    <div class="flex flex-col">
                      <label for="journey-to" class="pt-2">Kontakt</label>
                      <input disabled :placeholder=kontakt
                             class="rounded border-none bg-disabled-input pl-1.5 placeholder-text-black">
                    </div>
                  </div>
                  <div class="">
                    <div class="flex flex-col">
                      <label for="journey-from" class="pt-2">Öffnungszeiten</label>
                      <textarea disabled class="m-0 p-0 resize-none rounded
                      border-none bg-disabled-input pl-1.5 pb-[42%] pt-0 whitespace-normal">
                        {{oeffnungszeiten}}
                      </textarea>
                    </div>
                  </div>
                </div>
                <div class="flex flex-row gap-5 grid grid-cols-2">
                  <div class="flex flex-col">
                    <label for="journey-from" class="pt-2">Adresse</label>
                    <input disabled :value=adresse
                           class="rounded border-none bg-disabled-input pl-1.5 placeholder-text-black">
                  </div>
                  <div class="flex flex-col">
                    <label for="journey-to" class="pt-2">Kosten</label>
                    <input disabled :placeholder=kosten
                           class="rounded border-none bg-disabled-input pl-1.5 placeholder-text-black">
                  </div>
                </div>
                <label class="pt-2" for="journey-link">Beschreibung</label>
                <div class="flex flex-row justify-between gap-2">
                 <textarea disabled id="journey-from" type="text"
                           class="bg-disabled-input w-[100%] placeholder-text-black resize-none rounded pl-1.5
                           border-none focus:outline-none focus:ring-2 focus:ring-call-to-action whitespace-normal">
                   {{beschreibung}}
                </textarea>
                </div>
              </form>
            </div>
          </div>
        </Dialog>
        <div class="card">
          <div class="w-[100%] flex flex-col items-center justify-center">
            <div id="showDraggabeles" class="w-[85%] rounded-2xl bg-primary p-6">
              <div class="grid grid-cols-6 pb-3 justify-center items-center">
                <h2 class="col-span-2 font-nunito text-2xl text-text-black font-semibold">Aktivitäten</h2>
                <RouterLink :to='$route.fullPath + "/aktivitaet/neu"' class="col-start-6 bg-call-to-action
                rounded-3xl flex text-text-black font-nunito text-center items-center justify-center text-xl font-bold shadow-md hover:opacity-80"
                            v-tooltip.bottom="{
               value: 'Aktivität erstellen',
                 style:{
                   width: '30vw'
                 }}">
                  <AddActivityIllustration class="m-2 w-[20%]"/>
                  Erstellen
                </RouterLink>
              </div>
              <div id="planned-tasks"
                   class="flex flex-wrap bg-background p-1 planned-tasks rounded-md">
                <div v-for="activity in activities" class="flex">
                  <div v-if=!activity.added_to_calendar :id=activity.pk_activity_uuid
                       :data-event="JSON.stringify({title:activity.name,duration:formatTime(activity.estimated_duration/60)
                       ,editable:true,defId:activity.pk_activity_uuid,timeZone: 'local'})"
                       class="fc-event bg-secondary flex flex-col px-3 py-2 rounded-2xl m-2 cursor-grab">
                    <div class="font-semibold">{{ activity.name }}</div>
                    <div>{{ formatTime(activity.estimated_duration / 60) }}h</div>
                  </div>
                </div>
                <div v-if="noEvents" class="text-center justify-center w-[100%]">
                  <p class="font-nunito-sans text-base text-text-black py-3"> Keine Aktivitäten
                    vorhanden.</p>
                </div>
              </div>
              <p class="font-nunito text-base text-text-black font-semibold text-center pt-1"> Erstelle Aktivitäten
                und ziehe sie in deinen Plan, um deine Reise zu gestalten!</p>
            </div>
            <hr>
            <div class="w-[85%] rounded-2xl bg-primary p-6 mt-8">
              <div class="flex flex-row justify-between">
                <h2 class="font-nunito font-semibold text-2xl">Plan</h2>
              </div>
              <FullCalendar v-if="INITIAL_EVENTS.length > 0 || nothing_To_Render"
                            :options="calendarOptions"
                            class="px-4 bg-background rounded-md py-3"
              />
              <p class="font-nunito text-base text-text-black font-semibold text-center pt-1">Wird automatisch
                gespeichert! Aktivität anklicken, um alle Informationen zu sehen.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.planned-tasks > div {
  margin-bottom: 0.5em;
}
</style>
