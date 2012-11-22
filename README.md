VespolinaBillingBundle
==========

Introduction

After creating invoices or subscription plans it's important to ensure the money gets in.  Money can come though an automated process such as credit card billing or by means of manual processing (eg. pay by bank transfer).

The objective of this bundle is provide a solution for several billing scenarios.


* Bill a subscription plan

Mostly found with startups, you define several plans and let a customer choose from one.  By means of a batch processing they are billed or billed through a billing agreement with a payment provider.

* Billing invoices

Typically in B2B backend where invoices are sent to the customer upon the customer pays by bank transfer.  Follow up is of the essense here.

* One shot billing of a cart (check out process)

During a cart check out we can request direct billing (through a payment provider such as paypal) or perform offline billing (eg. bank transfer).

Mixed scenarios

Sometimes things get more tricky.  For instance, even if your customer has a subscription plan, he might want to buy an one time option for which he needs to pay aswell.  
Depending on the use case, he needs to pay it inmediatly (online billing) or have it charged the next time during the billing run.


The billing process

At a predefined time a plan based customer is ought to be billed.  Based on the chosen plan the bundle generates BillingRequest entities.
Each BillingRequest contains the earliest date the billing can be performed.  A background task looks for billing request which can be billed and executes the actual billing.
The BillingRequest contains all information needed to fulfill the billing such as the amount, a referencing entity (eg. the customer plan) and the billing party.
When a billing request is executed with a provider such as Paypal it creates a JMS payment instruction and tries to execute it wit Paypal.  If it succees the status of the BillingRequest is updated.  When the payment does not succeeds a new payment instruction should be created to reprocess the billing.


A BillingRequest doesn't need to be online, it might be an e-mail or printed letter requesting the customer to pay.

Monitoring billing

A sales clerk usually wants to have an overview of
* billing requests having an error
* the total amount still pending to be billed
* the total amount overdue









Dependencies:
==========
vespolina/core
vespolina/product
and maybe product-subscription but for now it's without




