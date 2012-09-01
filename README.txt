Service Administration Front End
SAFE is written by F. Overkamp, ObSimRef BV (C) 2009

For latest updates, see: http://www.obsimref.com/en/safe/

SAFE stands for Service and Administration Front End. It is a basic web-based application that allows small companies to perform invoicing tasks, both one-off invoices and recurring services (subscriptions). Subscriptions can be entered with a price per month, per quarter or per year. Invoice lines can be joined together by your company administrator into an invoice. That invoice can then be downloaded in PDF format, printed and sent to the customer. SAFE is meant to allow non-techical people to work with the invoicing system so simplicity is key.

Obtaining the latest sources: svn co http://pkg.obsimref.com/svn/safe/trunk safe

Why SAFE and not SugarCRM/PHPAGA/.... ?

We experimented with a number of publicly available open source applications. The main problem with all of them is that they are not purely administrative in function, and therefore present the users with many many other functions that will never be used. Ofcourse there is also the number of issues that we feel is not really done conveniently, but the clutter in the interface is really the primary issue.
Current state and roadmap

    * Working basic setup, based on phpMyEdit pages, expanded with PDF download and cron-scripts 
    * Next version will replace phpMyEdit pages with proper Zend framework usage

Features

    * Basic multilingual support
    * VAT and EU Intracom rules
    * Multiple subscriptions in 1 invoice or separate invoices per service: You decide
    * Processing of Call Detail Records in standard formats (i.e. to add voip services)
    * ... 

Requirements

    * PHP5
    * MySQL (working on other database abstraction)
    * Cron to enable automatic invoice-line creation

Licensing

SAFE is provided AS-IS without any warranty. It may be used, modified and redistributed under the Artistic License v2.

