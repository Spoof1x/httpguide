requests = {}

function requests:goto(link, body, callback)
    local req 
    if body then
        req = CreateHTTPRequestScriptVM("POST", link)
        local pl = json.encode(body)
        req:SetHTTPRequestRawPostBody('application/json', pl)
    else 
        req = CreateHTTPRequestScriptVM("GET", link)
    end
    req:SetHTTPRequestAbsoluteTimeoutMS(5000)
    req:SetHTTPRequestHeaderValue("Dedicated-Server-Key", "test")
    req:Send(function(result)
        info = {statuscode = result.StatusCode}
        if result.StatusCode == 200 then
            local data = json.decode(result.Body)
            if data then info.data = data else info.data = "nil" end
        end
        if callback then callback(info) end
    end)
end



