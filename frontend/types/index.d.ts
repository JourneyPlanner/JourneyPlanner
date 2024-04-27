export {};

declare global {
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
}
