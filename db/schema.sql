CREATE TABLE public.addresses (
    address_id integer NOT NULL,
    node_id integer NOT NULL,
    address_name character varying(255) NOT NULL,
    address_country character varying(255) NOT NULL,
    address_region character varying(255),
    address_city character varying(255) NOT NULL,
    address_street character varying(255) NOT NULL,
    address_house character varying(255) NOT NULL,
    address_entrance integer,
    address_apartment integer,
    address_create_date integer NOT NULL,
    address_update_date integer NOT NULL
);

CREATE TABLE public.nodes (
    node_id integer NOT NULL,
    user_id integer NOT NULL,
    node_name character varying(255),
    node_last_name character varying(255),
    node_patronymic character varying(255),
    node_company character varying(255),
    node_phone character varying(255) NOT NULL,
    node_email character varying(255),
    node_create_date integer NOT NULL,
    node_update_date integer NOT NULL,
    is_public boolean NOT NULL
);

CREATE TABLE public.users (
    user_id integer NOT NULL,
    user_login character varying(50) NOT NULL,
    user_passwd character varying(255) NOT NULL,
    user_reg_date integer NOT NULL,
    user_last_date integer NOT NULL
);

