<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Mpdf\Tag\P;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/new_request', function (Request $request) {
    if ($request->header('Authorization', false) !== "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IlRXU1QiLCJhZG1pbiI6dHJ1ZSwianRpIjoiOGQwZjcyNzMtOGFjYy00NGM1LWI4ODktZWNjMGNkY2FkYzRkIiwiaWF0IjoxNjA0MDkwNTU4LCJleHAiOjE2MDQwOTQxNzZ9.ASoMUJcPBm3JHmu0d25_p6OScxc1BzMyjSZ_wX7R8c0") {
        return response()->json(['message' => "unauthorized"], 401);
    } else {
        $auth = Http::post('https://api.joistapp.com/api/v6/sign_in.json', ["user" => ["email" => "thewaitstaffteam@gmail.com", "password" => env('joist_password')]]);
        if ($auth->status() === 200) {
            $joist_token = json_decode($auth->body());
        } else {
            return response()->json(["message" => "failed auth"], 401);
        }
        die(json_encode(Build_Request_Body()));        
        $response = Http::withHeaders([
            'X-Api-Authorization' => 'Joist-Token ' . $joist_token->auth_token,
        ])->post('https://api.joistapp.com/api/v6/194935/estimates', Build_Request_Body());
        if ($response->status() === 422) {
            return response()->json(["message" => "failed"], 422);
        }
        return response()->json(["message" => $response->body()]);
    }
});

function Build_Request_Body(): array
{
    $body = json_decode(request()->input('body'));
    return [
        "estimate" => [
            "qb_id" => null,
            "doc_id" => date("mdy-" . (date('G') * 3600 + date('i') * 60), strtotime($body->EVENT_DETAILS->DOE)),
            "name" => $body->CONTACT_INFO->first_name . " " . $body->CONTACT_INFO->last_name,
            "email" => $body->CONTACT_INFO->email,
            "notes" => Format_Notes($body),
            "issued_at" => gmdate("Y-m-d\TH:i:s\Z"),
            "show_total" => true,
            "show_price" => true,
            "show_quantity" => true,
            "phone1" => $body->CONTACT_INFO->phone,
            "contract_body" => "The WaitStaff Team Contract\n\n(\"The WaitStaff Team\" will be referred to from here on out as \"TWST\")\n\n--- GENERAL TERMS OF SERVICE ---\n- 4 continuous hour minimum per all staff members.\n- If client requests less staff than is recommended for an event, client understands that timeline may not be met and quality of work may be affected.\n- Client will NOT give out contact info OR receive any contact info of service staff sent through TWST staff for additional service not booked through TWST. Any hiring of a TWST staff member could be subject to a $1,000 \"Finders/Placement Fee\" if not otherwise arranged in writing between TWST and the client.\n- TWST reserves the right to refuse service to anyone for reasons including but not limited to:\n----- If the client does not wish to meet ratios set by TWST for staffing service.\n----- If the TWST staff are requested to perform unsafe, indecent, or unprofessional services while performing work.\n----- If TWST is booked for the date of the event.\n- By signing this document and/or TWST or any TWST staff receiving any money from the customer, the customer agrees to the services and conditions outlined in this contract.\n- If client hires a staff member to serve an event without alcohol involved, then requests staff to serve alcohol, applicable service hourly rates will be applied to the entirety of the staff's shift. Likewise if a service member who is hired for beer & wine only then requested to serve hard alcohol, the higher rate will be charged after the event is over.\n\n--- INSURANCE ---\n- TWST holds general liability, liquor liability for serving, and workman comp for the staff. TWST does not have \"Host Liquor\", client must supply the \"Host Liquor\" policy if the client or the facility that the event is being held at requires this.\n- ADDITIONALLY INSURED: If the client or the client's facility requires TWST to add on a facility as additionally insured, this request must be made in writing 2 weeks prior to an event with the email address that a copy of the policy must be sent to.\n\n--- PAYMENTS ---\n- BOOKING FEES: All booking fees are due upon booking to reserve an amount of staff for an event and are Non-Refundable.\n- URGENT BOOKING FEES: All events booked within 7 days of the event are subject to a $90 initial booking fee and $35 per staff member thereafter and are Non-Refundable.\n- RENTALS & SALES: All rentals and sales items are due 1 week prior to the event. If last minute or day of items are added on, these are due within 1 week of the events end.\n- STAFF HOURS: \n----- 75% of the staffing wages will be due 1 week prior to the event.\n----- Remainder will be due within 1 week of receipt for final payment. The client will receive an invoice for the remainder of hourly wages due that will account for any fluctuation based on the needs of the event and what the client has requested/approved for the night of. Any extensions of staff hours will be discussed with the client or a day of coordinator that the client has appointed on the day of the event by the lead TWST staff member.\n- LATE FEES: If payments are not made within 14 calendar days of when the request to pay the final invoice email was sent, a $25 late fee will be imposed. If the payment is made after calendar 30 days, the fee increases to $50 in total.\n- GRATUITY: Tip percentages are placed only on hourly wages and not added to booking fees or any other sales/rentals. Any other cash or other token gratuity (such as bottle of wine or other) is graciously accepted by the staff. \n----- BARTENDER GRATUITY: A 20% tip of the bartenders' total shift amount will be applied to the bill. The client may choose to allow or not allow an additional tip jar to be placed for guests to tip as well.\n----- SERVER GRATUITY: A 20% tip of the staff members' total shift amount will be applied to the bill. If you would like to add more gratuity or pay in cash the night of, simple let us know in writing and we will adjust the final bill. If cash given at the end of the event, we will confirm with the staff that they received the gratuity required. \n- ADDING ON STAFF: If additional staff are requested for an event after the initial booking and 7 days or more prior to the event, there will be our normal additional staff fee of $30 per staff member added on and is due upon the request. If an additional staff member is requested within 7 days of the event, the client will be subject to a $50 fee per staff member added.\n- MISSING STAFF: If for any reason a staff member does not show up and/or TWST cannot fulfill all staff spots that were booked/secured, the booking fee will be refunded for the amount of staff shifts that did not work as will any amounts that were paid for service hours not rendered. (This is in the case of last minute sickness or injury and TWST cannot cover the shift with another employee in short order.)\n- INCORRECT GUEST RATIOS FEE: In the event that there are more guests than are listed on the contract, TWST reserves the right to bill the client for the full amount of staff that should have been booked for the event based on a 1 to 35 staff to guest ratio. This includes the required booking fee, staffing hours and gratuity for the shift. The shift hours would be based off of the average of the other server shifts actually worked. These wages will be divvied up amongst the staff that worked as extra gratuity. \n\n---UNSAFE WORKING CONDITIONS---\n- In the event that the working conditions of the event are deemed unsafe by the staff, and/or are not in alignment with governmental produced safety guidelines, and/or violate our staff's being(whether that be racial, sexual, or physical assault by words or deeds), the staff are allowed to walk off the job and the client will be billed for the full amount of that staff's shift and gratuity for the billable hours they were there for or a 4 hour minimum, whichever is greater. \n---This includes a non-compliance with government produced guidelines regarding COVID19 and the phase openings for events and gatherings.",
            "contract_name" => "Contract - The WaitStaff Team(TWST) COVID19",
            "show_contact_signature" => false,
            "show_user_signature" => false,
            "days_to_pay" => 0,
            "total" => null,
            "change_order_total" => null,
            "sectioned" => false,
            "discount_percent" => null,
            "discount_amount" => null,
            "applied_discount" => null,
            "markup_percent" => null,
            "markup_amount" => null,
            "applied_markup" => null,
            "deleted" => false,
            "estimate_items_attributes" => Get_Items($body),
            "document_images_attributes" => [],
            "payment_requests_attributes" => [[
                "_destroy" => false,
                "amount" => ((Get_Total_Number_Of_People($body) + 1) * 35) + Get_Other_Deposits($body),
                "index" => 0,
                "label" => "Deposit",
                "payment_requestable_id" => null,
                "payment_requestable_type" => "Estimate",
                "percent" => null,
                "request_type" => "deposit",
                "deleted" => false
            ]]
        ]
    ];
}

function Format_Notes(object $object): string
{
    $string = "";
    foreach ($object as $category => $details) {
        if ($category !== "CONTACT_INFO") {
            if (is_object($details)) {
                $string .= "\n\n" . str_replace("_", " ", $category) . ":";
                foreach ($details as $detail_name => $details_detail) {
                    if (is_object($details_detail)) {
                        $string .= "\n   " . $detail_name . " : ";
                        foreach ($details_detail as $details_sub_cat => $details_sub_details) {
                            $string .= "\n      " . $details_sub_cat . " : " . $details_sub_details;
                        }
                    } else {
                        $string .= "\n" . $detail_name . " : " . $details_detail;
                    }
                }
            } else {
                $string .= "\n\n" . $category . " : " . $details;
            }
        }
    }
    return $string;
}

function Get_Total_Number_Of_People(object $object): int
{
    return
        Get_Total_Number_Of_Leads($object) +
        Get_Total_Number_Of_Server_Buffet($object) +
        Get_Total_Number_Of_Kitchen($object) +
        Get_Total_Number_Of_Server($object) +
        Get_Total_Number_Of_Private_Server($object) +
        Get_Total_Number_Of_BW_Bartenders($object) +
        Get_Total_Number_Of_Liquor_Bartenders($object) +
        Get_Total_Number_Of_Houseman($object);
}

function Get_Total_Number_Of_Leads(object $object): int
{
    if (
        strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Floor waitstaff") !== false
        || strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Buffet Staff") !== false
        || strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Kitchen Aids/Cooks") !== false
    ) {
        if ($object->EVENT_DETAILS->EVENT_TYPE !== "Graduation" && $object->EVENT_DETAILS->EVENT_TYPE !== "House Party") {
            if ($object->EVENT_DETAILS->EVENT_TYPE === "Cocktail Party") {
                if ($object->EVENT_DETAILS->GUEST_COUNT >= 100 && $object->EVENT_DETAILS->GUEST_COUNT < 200) {
                    return 1;
                } elseif ($object->EVENT_DETAILS->GUEST_COUNT < 100) {
                    return 0;
                } else {
                    return floor($object->EVENT_DETAILS->GUEST_COUNT / 200) + 1;
                }
            } else {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 200) + 1;
            }
        }
    }
    return 0;
}

function Get_Total_Number_Of_Server_Buffet(object $object): int
{
    if ($object->EVENT_DETAILS->EVENT_TYPE !== "Cocktail Party" && $object->EVENT_DETAILS->EVENT_TYPE !== "House Party" && $object->EVENT_DETAILS->EVENT_TYPE !== "Graduation") {
        if (strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Buffet Staff") !== false) {
            if ($object->EVENT_DETAILS->GUEST_COUNT > 200) {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 100);
            } else {
                return 1;
            }
        }
    }
    return 0;
}
function Get_Total_Number_Of_Kitchen(object $object): int
{
    if ($object->EVENT_DETAILS->EVENT_TYPE !== "Cocktail Party" && $object->EVENT_DETAILS->EVENT_TYPE !== "House Party" && $object->EVENT_DETAILS->EVENT_TYPE !== "Graduation") {
        if (strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Kitchen Aids/Cooks") !== false) {
            if (strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Family Style") !== false || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Full Service") !== false) {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 35) + 1;
            } else {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 100) + 1;
            }
        }
    }
    return 0;
}
function Get_Total_Number_Of_Server(object $object): int
{
    if ($object->EVENT_DETAILS->EVENT_TYPE !== "Cocktail Party" && $object->EVENT_DETAILS->EVENT_TYPE !== "House Party" && $object->EVENT_DETAILS->EVENT_TYPE !== "Graduation") {
        if (strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Floor waitstaff") !== false) {
            if (
                strpos(
                    $object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE,
                    "Tray Passing - Hors d'Ouevre"
                ) !== false
                && !strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Buffet - Hors d'Ouevre") !== false
                && !strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Buffet") !== false
                && !strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Family Style") !== false
                && !strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Full Service") !== false
            ) {
                if ($object->TWST_CONTRACTED_TO_HELP_WITH->DISHWARE_TYPE === "Disposable") {
                    return floor($object->EVENT_DETAILS->GUEST_COUNT / 75) + 1;
                } else {
                    return floor($object->EVENT_DETAILS->GUEST_COUNT / 50) + 1;
                }
            } elseif ((strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Buffet - Hors d'Ouevre") !== false
                    || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Buffet") !== false) &&
                (!strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Family Style") !== false
                    && !strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Full Service") !== false)
            ) {
                if ($object->TWST_CONTRACTED_TO_HELP_WITH->DISHWARE_TYPE === "Disposable") {
                    return floor($object->EVENT_DETAILS->GUEST_COUNT / 50) + 1;
                } else {
                    return floor($object->EVENT_DETAILS->GUEST_COUNT / 35) + 1;
                }
            } elseif (
                strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Family Style") !== false
                || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Full Service") !== false
            ) {
                if ($object->TWST_CONTRACTED_TO_HELP_WITH->DISHWARE_TYPE === "Disposable") {
                    return floor($object->EVENT_DETAILS->GUEST_COUNT / 35) + 1;
                } else {
                    return floor($object->EVENT_DETAILS->GUEST_COUNT / 20) + 1;
                }
            }
        }
    }
    return 0;
}

function Get_Total_Number_Of_Private_Server(object $object): int
{
    if ($object->EVENT_DETAILS->EVENT_TYPE === "Cocktail Party" || $object->EVENT_DETAILS->EVENT_TYPE === "House Party" || $object->EVENT_DETAILS->EVENT_TYPE === "Graduation") {
        if (strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Floor waitstaff") !== false || strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Buffet Staff") !== false || strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Kitchen Aids/Cooks") !== false) {
            if (
                strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Family Style") !== false
                || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Full Service") !== false
            ) {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 20) + 1;
            } else {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 25) + 1;
            }
        }
    }
    return 0;
}

function Get_Total_Number_Of_Houseman(object $object): int
{
    return floor($object->EVENT_DETAILS->GUEST_COUNT / 200);
}

function Get_Total_Number_Of_BW_Bartenders(object $object): int
{
    if (strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Bartender(s)") !== false) {
        if (
            (strpos($object->TWST_CONTRACTED_TO_HELP_WITH->BEVERAGE_SERVICE, "Beer (Bottles)") !== false
                || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->BEVERAGE_SERVICE, "Beer (Kegs)") !== false
                || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->BEVERAGE_SERVICE, "Wine") !== false) &&
            (!strpos($object->TWST_CONTRACTED_TO_HELP_WITH->BEVERAGE_SERVICE, "Signature Cocktails (1-4 pre-determined drinks)") !== false
                && !strpos($object->TWST_CONTRACTED_TO_HELP_WITH->BEVERAGE_SERVICE, "Full Cocktail Bar") !== false)
        ) {

            if (
                strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Family Style") !== false
                || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Full Service") !== false
            ) {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 80) + 1;
            } else {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 100) + 1;
            }
        }
    }
    return 0;
}
function Get_Total_Number_Of_Liquor_Bartenders(object $object): int
{
    if (strpos($object->SERVICES_REQUESTED->STAFF_REQUESTED, "Bartender(s)") !== false) {
        if (
            strpos($object->TWST_CONTRACTED_TO_HELP_WITH->BEVERAGE_SERVICE, "Signature Cocktails (1-4 pre-determined drinks)") !== false
            || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->BEVERAGE_SERVICE, "Full Cocktail Bar") !== false
        ) {
            if (
                strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Family Style") !== false
                || strpos($object->TWST_CONTRACTED_TO_HELP_WITH->FOOD_SERVICE, "Plated - Full Service") !== false
            ) {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 50) + 1;
            } else {
                return floor($object->EVENT_DETAILS->GUEST_COUNT / 80) + 1;
            }
        }
    }
    return 0;
}

function Get_Items(object $object): array
{
    $items = [Get_Booking_Fee_Item($object)];
    $items = array_merge($items,Get_People_Items($object));
    return $items;
}

function Get_People_Items(object $object)
{
    $items = [];
    ForEach(Get_Lead_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    ForEach(Get_Server_Buffet_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    ForEach(Get_Kitchen_Personnel_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    ForEach(Get_Server_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    ForEach(Get_Private_Server_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    ForEach(Get_Houseman_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    ForEach(Get_BW_Bartender_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    ForEach(Get_Liquor_Bartender_Items($object) as $lead_item)
    {
        $items[] = $lead_item;
    }
    return $items;
}

function Get_Booking_Fee_Item(object $object): array
{
    return [
        "_destroy" => false,
        "name" => "DOB - Booking fee - " . date('Y'),
        "unit" => null,
        "price" => ((Get_Total_Number_Of_People($object) + 1) * 35) + Get_Other_Deposits($object),
        "quantity" => "1",
        "fixed_price" => null,
        "index" => 1,
        "notes" => "$70 for first staff person + $35 per each additional staffsing\n",
        "sample" => false,
        "user_id" => 194935,
        "deleted" => false,
        "estimate_id" => null,
        "estimate_item_taxes_attributes" => []
    ];
}

function Get_Other_Deposits(object $object): float
{
    if (strpos($object->SERVICES_REQUESTED->PACKAGES_REQUESTED, "Portable Bar") !== false) {
        return 50.00;
    }
    return 0.00;
}

function Get_Lead_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_Leads($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Lead",
            "notes"=> "Event lead to manage staff and time line\n[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "50",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}

function Get_Server_Buffet_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_Server_Buffet($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Buffet Server",
            "notes"=> "Food service only (alcohol service prohibited)\n[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "35",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}

function Get_Kitchen_Personnel_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_Kitchen($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Cook / Kitchen aid",
            "notes"=> "Food service only (alcohol service prohibited)\n[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "30",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}

function Get_Server_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_Server($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Server",
            "notes"=> "Food service only (alcohol service prohibited)\n[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "35",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}

function Get_Private_Server_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_Private_Server($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Private Party Server",
            "notes"=> "General party maintenance\nFood prep and minimal cooking\nSet up & Clean up\n[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "40",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}

function Get_Houseman_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_Houseman($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Houseman",
            "notes"=> "[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "30",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}

function Get_BW_Bartender_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_BW_Bartenders($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Bartender - B/W",
            "notes"=> "Beer, wine, champagne, no mixed drinks\n[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "45",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}

function Get_Liquor_Bartender_Items(object $object) : array
{
    $x = 0;
    $items = [];
    while($x++ < Get_Total_Number_Of_Liquor_Bartenders($object))
    {
        $items[] = [
            "fixed_price"=> null,
            "index"=> 0,
            "name"=> "HRS - Bartender - Liquor",
            "notes"=> "All Alcohol Service\n[start_time] - [end_time]\n+ [drive_time] HRS Drive Time",
            "price"=> "50",
            "quantity"=> "0",
            "sample"=> false,
            "unit"=> null,
            "user_id"=> 194935,
            "_destroy"=> false,
            "deleted"=> false,
            "estimate_id"=> null,
            "estimate_item_taxes_attributes"=> [[
                "amount" => "20",
                "deleted" => false,
                "estimate_item_id" => null,
                "name" => "20% Tip",
                "tax_id" => "313617",
                "user_id" => 194935,
                "_destroy" => false
            ]]
        ];
    }
    return $items;
}
