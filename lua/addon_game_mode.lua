require("webtool")
if CAddonTemplateGameMode == nil then
	CAddonTemplateGameMode = class({})
end
local link = "http://localhost"

function Precache( context )
	--[[
		Precache things we know we'll use.  Possible file types include (but not limited to):
			PrecacheResource( "model", "*.vmdl", context )
			PrecacheResource( "soundfile", "*.vsndevts", context )
			PrecacheResource( "particle", "*.vpcf", context )
			PrecacheResource( "particle_folder", "particles/folder", context )
	]]
end

-- Create the game mode when we activate
function Activate()
	GameRules.AddonTemplate = CAddonTemplateGameMode()
	GameRules.AddonTemplate:InitGameMode()
end

function CAddonTemplateGameMode:InitGameMode()
	GameRules:GetGameModeEntity():SetThink( "OnThink", self, "GlobalThink", 2 )
	ListenToGameEvent("player_chat", Dynamic_Wrap(self,"OnChat"), self)
end

-- Evaluate the state of the game
function CAddonTemplateGameMode:OnThink()
	if GameRules:State_Get() == DOTA_GAMERULES_STATE_GAME_IN_PROGRESS then
		--print( "Template addon script is running." )
	elseif GameRules:State_Get() >= DOTA_GAMERULES_STATE_POST_GAME then
		return nil
	end
	return 1
end


function CAddonTemplateGameMode:OnChat(keys)
	local text = string.lower(keys.text)
	local playerID = keys.playerid
	local hero = PlayerResource:GetSelectedHeroEntity(playerID)


	if text == "-save" then
		local steamid = PlayerResource:GetSteamAccountID(playerID)
		items = GetItemList(hero)
		requests:goto( link.."/api/inventory/index.php?steamid="..steamid .. "&method=save", items, function (result)
			DeepPrintTable(result)
		end)
	elseif text == '-load' then
		local steamid = PlayerResource:GetSteamAccountID(playerID)
		-- send GET request withcallback processing 
		local body = requests:goto(link.."/api/inventory/index.php?steamid="..steamid.. "&method=load", nil, function(result)
		-- callback processing (data - response object)
		local data = result.data
		local eslot = nil
		for slot=0,5 do
			print(data['slot'..slot])
			-- remove anything in current slot
			local iItem = hero:GetItemInSlot(slot)
			if iItem then hero:RemoveItem(iItem) end

			-- add item to slot
			local item = hero:AddItemByName(data['slot'..tostring(slot)])

			-- rearrange slot
			if item then
				if eslot and eslot~=slot then	
					hero:SwapItems( eslot, slot )
				end
			elseif not eslot then
				eslot = slot
			end
		end
		end)
	end
end




function GetItemName(hero, slot)
    local item = hero:GetItemInSlot(slot)
    if item then
        local itemName = item:GetAbilityName()
        return itemName
    else
        return ""
    end
end

function GetItemList(hero)

    local item
    local itemTable = {}

    for i=0,5 do
        item = GetItemName(hero,i)
        table.insert(itemTable,i,item)
    end

    return itemTable
end