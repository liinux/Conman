<?php
	class OrdersvaluesModel extends Model {
		function addOrderValue($values)
		{
			$this->db->query("INSERT INTO orders_values (order_id, order_alternative_id, value) VALUES ('%s','%s','%s')", $values['order_id'], $values['order_alternative_id'], $values['value']);
		}
		function getOrderValuesFromOrder($orderid)
		{
			return $this->db->query("SELECT orders_alternatives.id, orders_values.id as value_id, orders_alternatives.name, orders_alternatives.cost, orders_values.given 
			FROM orders_values
			INNER JOIN orders_alternatives ON orders_values.order_alternative_id = orders_alternatives.id
			WHERE order_id = '%s'", $orderid);
		}
		
		function listOrderValues()
		{
			return $this->db->query("SELECT order_id, orders_alternatives.name, orders_alternatives.cost, value
			FROM orders_values
			INNER JOIN orders_alternatives ON orders_values.order_alternative_id = orders_alternatives.id
			INNER JOIN orders ON orders.id = orders_values.order_id 
			WHERE orders.status = 'COMPLETED'");
		}
		
		function markGiven($id)
		{
			$this->db->query("UPDATE orders_values SET given = 1 WHERE id = '%s'", $id);
		}
	}
?>