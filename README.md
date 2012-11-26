VespolinaBillingBundle
==========

## Introduction

After creating invoices or subscription plans it's important to ensure the money gets in.  Money can come though an automated process such as credit card billing or by means of manual processing (eg. pay by bank transfer).

The objective of this bundle is provide a solution for several billing scenarios.


## Billing subscriptions / plans

Mostly found with startups, you define several plans and let a customer choose from one.  By means of a batch processing they are billed or billed through a billing agreement with a payment provider.
Depending on the business context a customer can have more then one plan.  While github.com maintains a plan per user approach a typical hosting company will have a plan per service instance approach.

### Billing invoices

Typically in B2B backend where invoices are sent to the customer upon the customer pays by bank transfer.  Follow up is of the essense here.

### One shot billing of a cart (check out process)

During a cart check out we can request direct billing (through a payment provider such as paypal) or perform offline billing (eg. bank transfer).

### Mixed scenarios

Sometimes things get more tricky.  For instance, even if your customer has a subscription plan, he might want to buy an one time option for which he needs to pay aswell.  
Depending on the use case, he needs to pay it inmediatly (online billing) or have it charged the next time during the billing run.


## The billing process : billing requests and statements

### Generating billing requests

At a predefined time a plan based customer is ought to be billed.  Based on the chosen plan the bundle generates BillingRequest entities.
Each BillingRequest contains the earliest date the billing can be performed.  A background task looks for billing request which can be billed and executes the actual billing.
The BillingRequest contains all information needed to fulfill the billing such as the amount, a referencing entity (eg. the customer plan) and the billing party.
When a billing request is executed with a provider such as Paypal it creates a JMS payment instruction and tries to execute it with Paypal.  If it succeeds the status of the BillingRequest is updated.  When the payment does not succeed a new payment instruction should be created to reprocess the billing.

A BillingRequest doesn't need to be online, it might be an e-mail or printed letter requesting the customer to pay.  In such a scenario the bank transfer should contain a reference to the billing request by means of an unique code.

### Generating billing statements

Billing requests are rather technical objects to faciliate tracking of payments.  A billing statement is rather a legal statement such as an invoice which is periodically issued.  The number of billing requests and statements for a given customer do not need to match:  while billing monthly a billing statement could be issued three-monthly which is often the case.

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
vespolina/core

vespolina/product

jms/payment-core-bundle

and maybe product-subscription but for now it's without




