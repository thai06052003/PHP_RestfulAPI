/* fetch('http://localhost:8000/api/v1/users').then((res)=> res.json()).then((users) => {
    console.log(users)
}) */
const data = {
    name: "User 3",
    email: "user3@gmail.com",
    password: "123123"
}
fetch('http://localhost:8000/api/v1/users', {
    method: 'POST',
    headers: {
        "Content-Type": "application/json"
    },
    body: JSON.stringify(data),
})
.then((res) => res.json())
.then((users) => {
    console.log(users)
})