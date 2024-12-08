export {};

declare global {
    interface Journey {
        id: string;
        name: string;
        destination: string;
        invite: string;
        mapbox_full_address: string;
        mapbox_id: string;
        from: string;
        to: string;
        role: number;
    }

    interface Feature {
        type: string;
        geometry: {
            coordinates: number[];
            type: string;
        };
        properties: {
            name: string;
            mapbox_id: string;
            feature_type: string;
            address: string;
            full_address: string;
            place_formatted: string;
            name_preferred: string;
            context: {
                country: {
                    id: string;
                    name: string;
                    country_code: string;
                    country_code_alpha_3: string;
                };
                postcode: {
                    id: string;
                    name: string;
                };
                place: {
                    id: string;
                    name: string;
                };
                locality: {
                    id: string;
                    name: string;
                };
                address: {
                    id: string;
                    name: string;
                    address_number: string;
                    street_name: string;
                };
                street: {
                    id: string;
                    name: string;
                };
            };
            coordinates: {
                latitude: number;
                longitude: number;
                routable_points: {
                    name: string;
                    latitude: number;
                    longitude: number;
                }[];
            };
            language: string;
            maki: string;
            poi_category: Array<string>;
            poi_category_ids: Array<string>;
            external_ids: {
                safegraph: string;
                foursquare: string;
            };
            metadata: {
                primary_photo: string;
            };
            operational_status: string;
        };
    }

    interface MapBoxRetrieveEvent extends Event {
        detail: {
            features: Feature[];
            attribution: string;
        };
    }

    interface User {
        id: string;
        display_name: string;
        username: string;
        role: number;
    }

    interface repeatType {
        name: string;
        value: number;
    }

    interface Activity {
        address: string;
        mapbox_full_address: string;
        calendar_activities: CalendarActivity[];
        id: string;
        cost: string;
        created_at: string;
        description: string;
        email: string;
        estimated_duration: string;
        journey_id: string;
        latitude: string;
        longitude: string;
        link: string;
        mapbox_id: string;
        name: string;
        opening_hours: string;
        phone: string;
        updated_at: string;
        repeat_type: string;
    }

    interface CalendarActivity {
        id: string;
        title: string;
        start: string;
        end: string;
        allDay: boolean;
        activity_id: string;
    }

    interface Template {
        id: string;
        name: string;
        description: string;
        destination: string;
        length: number;
        mapbox_full_address: string;
        users: User[];
        from: string;
        to: string;
    }
}
