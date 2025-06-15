 Recommender System
Hybrid Rule-Based and Collaborative Filtering Recommender System for Fair Beneficiary Selection in MSWDO Programs

To recommend qualified and deserving beneficiaries for government assistance programs using a combination of eligibility rules and transaction history to ensure fair and data-driven aid distribution.


Rule-Based Filtering: Check income, dependents, sector

Collaborative Filtering: Check past transactions (who received aid, how often)

Recommend based on:
beneficiaries profile 
this is the data in their profile maybe this will help
-beneficiaries table
#id
full_name
birthday
age
gender
civil_status
birthplace
education
occupation
religion
barangay
sitio
date_added
created_at

they have also relatives that are stored in database i will give them to you also
-relatives table
id
beneficiary_id
name
age
civil_status
relationship
educational_attainment
occupation
created_at

# maybe this will help too the transactions table
-transactions table
`id`, `beneficiary_id`, `patient_name`, `patient_age`, `relation`, `patient_gender`, `patient_civil_status`, `patient_birthday`, `patient_birthplace`, `patient_education`, `patient_occupation`, `patient_religion`, `patient_sitio`, `patient_barangay`, `patient_complete_address`, `client_name`, `client_age`, `client_gender`, `client_civil_status`, `client_birthday`, `client_birthplace`, `client_education`, `client_occupation`, `client_religion`, `client_sitio`, `client_barangay`, `client_complete_address`, `request_type`, `request_purpose`, `request_date`, `amount`, `diagnosis_school`, `prep_by`, `pos_prep`, `not_by`, `pos_not`, `id_type`, `created_at`,

-programs table
`id`, `program_name`, `program_type`, `program_description`, `created_at`, `updated_at`, `target_beneficiaries`


Least aid received (or longest ago)

Output a ranked list of suggested beneficiaries