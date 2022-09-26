## How to start

- Docker must be installed on systen which support docker compose version 3.7 
- Clone project from git
- go to project directory using a terminal
- run the [script.sh] file in terminal using command [ bash script.sh ] OR run the commands from script.sh file in terminal
- I have used insomnia for Api collection, please download insomnia core in the system and import [api_demo.json] from project folder.

## Asumption and choices made
From the document provided to build the demo, I found that loan repayment is Zero interest emi system, So I did not include any interest calculation.

I have assumed settlement of entire loan will be on last installment.
Like total loan amount $10000 for 3 weakly emi is $3333.33, $3333.33, $3333.34 can be paid in emi as stated or in $4000,$3500,$2500 way.

## Super Admin Credential 
{
"email":"superadmin@demo.com",
"password":"123456"
}

## Customer Credentials
Find customer email from api "http://localhost:8000/api/user/list"
and password = password
