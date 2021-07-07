# spravochnik-api
JSON REST API using Phalcon 4.0

Implementation of an API application using the Phalcon Framework [https://phalcon.io](https://phalcon.io)
### General

#### JWT Tokens
[JWT](https://jwt.io) is used to secure the API. 

#### JSONAPI
All responses are formatted according to the [JSON API](https://jsonapi.org) standard

### Usage

Stable api version is `1.1`. Use `https://example.com/api/1.1/`

#### Requests
The routes available are:

**NOTE** `{Id}` in route replace by Id respective entity. `0` may have special meaning, see [WiKi](/wiki)  

| Method  | Route                     | Parameters                 | Action                                      |
|---------|---------------------------|----------------------------|---------------------------------------------|
| `POST`  | `/auth`                   | `userLogin`, `userPassword`| Authorization - get JWT Token               |
| `POST`  | `/registry`               | `userLogin`, `userPassword`| Registration - create user account          |
| `GET`   | `/users`                  |                            | Get all users data                          |
| `GET`   | `/users/{Id}`             | Numeric Id                 | Get user data            |                  |
| `PATCH` | `/users/{Id}`             | Numeric Id                 | Update user data                            |
| `GET`   | `/nodes`                  |                            | Get all current user directory nodes        |
| `POST`  | `/nodes`                  |                            | Create directory node for current user      |
| `GET`   | `/nodes/{Id}`             | Numeric Id                 | Get directory node                          |
| `PATCH` | `/nodes/{Id}`             | Numeric Id                 | Update directory node                       |
| `DELETE`| `/nodes/{Id}`             | Numeric Id                 | Delete directory node                       |
| `GET`   | `/nodes/public`           |                            | Get all public directory nodes              |
| `GET`   | `/nodes/{Id}/addresses`   | Numeric Id                 | Get all related addresses of directory node |
| `GET`   | `/addresses/{Id}`         | Numeric Id                 | Get addresses                               |
| `GET`   | `/addresses?node[Id]={Id}`| Numeric Id                 | Create address                              |
| `PATCH` | `/addresses/{Id}`         | Numeric Id                 | Update address                              |
| `DELETE`| `/addresses/{Id}`         | Numeric Id                 | Delete address                              |

#### Pagination

**NOTE** `{pageSize}` is replaced by the number of items on one page, `{pageNum}` by the page number (greater than 0) 

`/users?page[size]={pageSize}&page[num]={pageNum}`

`/nodes?page[size]={pageSize}&page[num]={pageNum}`

`/nodes/public??page[size]={pageSize}&page[num]={pageNum}`

`/nodes/{Id}/addresses?page[size]={pageSize}&page[num]={pageNum}`

#### Responses
##### Structure
- `jsonapi` Contains the `version` of the API as a sub element
- `data` Data returned. Is not present if the `errors` is present
- `errors` Collection of errors that occurred in this request. Is not present if the `data` is present
- `meta` Contains `timestamp` and `hash` of the `json_encode($data)` or `json_encode($errors)` 

**NOTE** After a `GET` the API will always return a collection of records, even if there is only one returned. If no data is found, an empty result will be returned.

#### Response example:
**Success response**
```json
{
    "jsonapi": {
        "version": "1.0"
    },
    "data": [{
        "token": "dfhershstdghnrhnxrh",
        "userId": 4,
        "setIn": "2021-07-07T13:14:54+03:00",
        "expiresIn": "2021-07-07T15:14:54+03:00"
    }],
    "meta": {
        "timestamp": "2021-07-07T13:14:54+03:00",
        "hash": "58e8480aa90aa6918454dead369039c3203ac3b5"
    }
}
```
**Error response**

```json
{
    "jsonapi": {
        "version": "1.0"
    },
    "errors": [
        "No user data transferred"
    ],
    "meta": {
        "timestamp": "2021-07-07T13:14:54+03:00",
        "hash": "58e8480aa90aa6918454dead369039c3203ac3b5"
    }
}
```

##### Records Structure
**Users**
```json
{
    "type": "users",
    "id": 4,
    "attributes": {
        "user_id": 11,
        "user_login": "stalin",
        "user_reg_date": "2021-07-05T23:59:36+03:00",
        "user_last_date": "2021-07-07T13:14:54+03:00"
    },
    "links": {
        "self": "https:\/\/example.com\/api\/1.1\/users\/1"
    }
}
```
**Nodes**
```json
{
    "jsonapi": {
        "version": "1.0"
    },
    "data": [{
        "type": "nodes",
        "id": 12,
        "attributes": {
            "node_id": 12,
            "user_id": 411,
            "node_name": "Yuri",
            "node_last_name": "Gagarin",
            "node_patronymic": "Alekseevich",
            "node_company": "SSSR",
            "node_phone": "663362",
            "node_email": "-",
            "node_create_date": "2021-07-07T13:27:09+03:00",
            "node_update_date": "2021-07-07T13:27:09+03:00",
            "is_public": true
        },
        "links": {
            "self": "https:\/\/example.com\/api\/1.1\/nodes\/12"
        }
    }],
    "meta": {
        "timestamp": "2021-07-07T13:27:09+03:00",
        "hash": "71bb7927913dd33e486da155dadef3b6ec2eda6f"
    }
}
```
**Addresses**
```json
{
    "jsonapi": {
        "version": "1.0"
    },
    "data": [{
        "type": "addresses",
        "id": 51,
        "attributes": {
            "address_id" : 51,
            "node_id" : 11,
            "address_name": "Job", 
            "address_country":"Russia",  
            "address_region":"Moscow oblast", 
            "address_city" :"Moscow",
            "address_street" :"Lenina",
            "address_house" :7,
            "address_entrance" :3,
            "address_apartment" :43,
            "address_create_date":"2021-07-07T13:27:09+03:00",
            "address_update_date":"2021-07-07T13:27:09+03:00"
        },
        "links": {
            "self": "https:\/\/example.com\/api\/1.1\/addresses\/51"
        }
    }],
    "meta": {
        "timestamp": "2021-07-07T13:27:09+03:00",
        "hash": "71bb7927913dd33e486da155dadef3b6ec2eda6f"
    }
}
```