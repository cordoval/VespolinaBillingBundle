VespolinaBillingBundle
==========

## Introduction

Billing is about sending billing requests to customers for goods or services.

This bundle provides functionalities to bill customers, initiate and process payment requests, follow up payment requests and handle payment issues.   

Payments can be invoked synchronous (eg. Paypal, Visa, Mastercard) and asynchronous (eg. bank transfers).  In the latter case bank statements need to be imported into the billing system.

Furthermore the bundle should initiate dunning processes to assure payments are retrieved and services are deactivated if the customer refuses to pay.



## Billing Scenarios

### Billing of Service Plans

Many service based companies allow customers to choose a service (or subscription) plan to pay for the service used.  Depending on the business context a customer can have more then one plan.  While github.com maintains a plan per customer approach a typical hosting company will have a plan per server instance approach.

### Billing of Invoices

In the context of B2B the customer is often allowed to pay after he receives his goods or service and associated invoice.  The billing process assures that the invoice is paid.  

### Offline Cart Checkout Payments

During a cart check out goods might be bought perform offline payments (eg. bank transfer).  The billing bundle guides offline processing.


### Mixed scenarios

Sometimes things get more tricky.  For instance, even if your customer has a subscription plan, he might want to buy an one time option for which he needs to pay aswell.  
Depending on the use case, he needs to pay it inmediatly (online billing) or have it charged the next time during a billing run.


## The billing process : billing requests and statements

### What is a billing request?

At a predefined time a plan based customer is ought to be billed.  Based on the chosen plan the bundle generates BillingRequest entities.  

> The billing request states the intention to charge the customer on a planned date.

Each billing request contains all required information needed to fulfill the billing process.  It includes amongst the amount to be charged, the earliest date a particular customer can be billed,  a referencing entity (eg. the customer plan) and the billing party and gateway. 

It's important to realize that there are two phases in a billing run.  The first phase consists of generating billing requests and  the next phase consists off initiating and executing payment requests to the various payment gateways. 


### Generating billing requests

Billing requests are typically created for active service plans.  A scheduled background process is used to generate billing requests and determine the date upon which the payment request should be initiated.  

Optionally the creation of a billing request and inmediate execution of the payment request can be configured to be done right after each other.

#### Generation Strategies

The billing bundle supports two strategies of generating billing requests: **multi period**, **one period**.

##### Multi period

With this strategy background process generates for each customer multiple billing requests over a longer period of time.  A plan for a year with monthly billing would generate twelve billing requests.

##### One period

With **one period** the generation of the billing requests is only performed for the current or next service period. 

Typically companies billing every month will generate billing requests one week before the payment execution happens.  This allows administration to verify and potentially correct billing requests in a timely matter before a payment request is initiated.


##### Which approach should be chosen?

**One period** should be chosen if the the customer is expected to change often his mind or the expected duration of the subscription is limited.  It reduces the amount of manual (verification) work to cancel or adjust generated billing requests.  The drawback of just-in-time is that it's harder to report expected revenues. 

If customers are expected to have a more long term commitment (eg. server hosting) it might be better to go with **multi period** billing requests.  As a bonus expected revenues can be more accurately forecasted.
 


### Executing billing requests


When a billing request is executed the system will create a payment instruction (if relevant) and contact the payment gateway to charge the customer.  

When a payment fails a new payment instruction is created and linked to the billing request.  Hence one billing request can be associated with multiple payment instructions.

##### Generation vs execution

Depending on the business context you can enable or disable **just in time** billing execution.  

With **just-in-time** enabled the payment request is executed directly after a billing request has been created and the planned billing date is today.  

##### Payment feedback

If the payment gateway supports synchronous feedback (such as Paypal) things are pretty easy.  If a payment request fails the status of the billing request can be inmediatly updated and administration can be notified.  

Things become more tricky with asynchronous feedback.  Bank cheques, wired payments, bank transfers all have a delay of days to weeks.  

To make things worse often horrible bank statements need to be imported in order to update payment information.  Although it's not possible to support every bank format a generic interface will be provided to update payment details. 


##### Notifications

A Billing request can be communicated to the customer by e-mail or by printed letter if required by the country's law. 


### Billing statements

Billing requests are rather technical objects to faciliate tracking of payments.  A billing statement is rather a legal statement such as an invoice which is periodically issued.  The number of billing requests and statements for a given customer do not need to match:  while billing monthly a billing statement could be issued three-monthly.

### Monitoring the billing process

A sales clerk usually wants to have an overview of
* billing requests having an error
* offline billing requests 
* the total amount still pending to be billed
* the total amount overdue


## The model

### Anatomy of a plan / service

A plan should contain following information relevant for billing:
* name
* version
* amount
* interval type (day, month, year)
* interval amount ( 1-month, 2-months = bi-monthly, 1-year, ...)
* occurences (how many times the BR should be executed, 0 means infinite)

### Anatomy of a billing request

A billing should contain following information:

* the partner being billed
* amount
* planned execution date
* state of the payment
* payment gateway (how will it be billed)
* reference to the service to be paid (if a customer has only one plan at most)
* consumption data ; A key / value pair containing collected metrics, eg. server traffic volume
* audit trail ...

Optionally:

* the partner issuing the bill (handy for organisations with multiple legal organisations (eg. multi-country)
* billing request items: a collection BillingRequestItemInterface instances which reference individual services and amounts to be billed.
  A billing request item contains:
  * reference to the service to be paid
  * consumption data
  * amount to be charged


### Advanced billing processess

## Changing plans (upgrade / downgrade)

Changing a plan requires adjusting open billing requests.  The administration should choose if the change is performed as Prorated or Non-prorate.

With Prorated the actual consumption of the product is charged (on a day basis).  If a customer is charged for a service $30 in April and he wants to go to a $60 plan on the 10th of April following will be billed:
day 1->10 :  $30 / 30 days  * 10 days = $10
day 16->30:  $60 / 30 days  * 20 days = $40

Total: $50 will be charged.  Then open billing request is cancelled and new billing requests are created, for which the first will $50 and others $60.
With non-Prorated the billing request is cancelled and a new billing request is greated with the new amount.

In both cases the new contract start date of the new plan should be one day after the previous one (trust me on this, it's an accounting nightmare to cope with product changes on the same day).



## Dunning & legal recovery

When a billing request expires the dunning process kicks in.  Starting with sending notifications with the amount overdue to deactivating the service and persuing legal recovery.
A simple configuration should be provided to model the dunning process.  Below an example configuration:

* 0 -> 14d overdue: send every 5 days notification "warning amount overdue"
* 15 -> 30d overdue: send every 5 days notification "warning amount overdue - less friendly"
* 31 -> 50d overdue: escalate to legal recovery department
* 51d overdue: de-activate service



Other functionalities this bundle should have

* Provide means to process bank statement to match open billing requests
* Allow the customer to resume a failed payment by sending him an email containing a link to perform online billing.
* Cope with customers paying multiple billing requests at once (eg. billing request A is not paid but bank transfer of billing request B contains the amount of A + B).
* Changing a plan might require refunding the customer or charging extra.
* Change billing plan periods








Dependencies:
==========
* vespolina/core
* vespolina/product
* jms/payment-core-bundle

and maybe product-subscription but for now it's without




