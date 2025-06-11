// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract TradeContract {
    struct Trade {
        address trader;
        string tradeType;
        uint256 amount;
        uint256 timestamp;
    }

    Trade[] public trades;

    event TradeExecuted(address indexed trader, string tradeType, uint256 amount, uint256 timestamp);

    function executeTrade(string memory _tradeType, uint256 _amount) public {
        require(_amount > 0, "Trade amount must be > 0");
        require(bytes(_tradeType).length > 0, "Trade type required");

        Trade memory newTrade = Trade(msg.sender, _tradeType, _amount, block.timestamp);
        trades.push(newTrade);
        emit TradeExecuted(msg.sender, _tradeType, _amount, block.timestamp);
    }

    function getTradeCount() public view returns (uint256) {
        return trades.length;
    }

    function getTrade(uint256 _index) public view returns (address, string memory, uint256, uint256) {
        require(_index < trades.length, "Trade does not exist");
        Trade storage trade = trades[_index];
        return (trade.trader, trade.tradeType, trade.amount, trade.timestamp);
    }
}
